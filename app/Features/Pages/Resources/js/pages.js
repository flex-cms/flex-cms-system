import { html } from "lit";

import {
    ApiTableDataSource,
    TableColumn,
} from "@admin-ui/components/table/index.js";

function initializePagesTable() {
    const table = document.querySelector("#pages-table");

    if (!table || table.dataset.initialized === "true") {
        return;
    }

    table.dataset.initialized = "true";

    table.configure({
        rowKey: "id",
        hoverable: true,
        paginated: true,
        searchable: true,
        searchPlaceholder: "Търсене по име или URL адрес...",
        searchDebounce: 300,
        page: 1,
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        sortBy: "position",
        sortDirection: "asc",
        selectable: true,
        bulkActions: (currentTable) => pageBulkActions(currentTable),
        filterDefinitions: [
            {
                key: "status",
                label: "Статус",
                type: "select",
                options: [
                    { value: "", label: "Всички" },
                    { value: "active", label: "Активни" },
                    { value: "inactive", label: "Неактивни" },
                    { value: "deleted", label: "В кошчето" },
                ],
            },
        ],
        rowActions: (row) => pageActions(row),
        emptyTitle: "Няма страници",
        emptyDescription:
            "Няма страници, които отговарят на текущите условия.",
        columns: [
            column({
                key: "display_name",
                label: "Страница",
                sortable: true,
                render: (value, row) => html`
                    <button
                        type="button"
                        class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300"
                        @click=${() => visit(`/admin/pages/edit/${row.id}`)}
                    >
                        ${value}
                    </button>
                `,
            }),
            column({
                key: "full_slug",
                label: "Път",
                sortable: true,
                render: (value) => html`
                    <code class="text-xs text-slate-600 dark:text-slate-300"
                        >/${value}</code
                    >
                `,
            }),
            column({
                key: "status",
                label: "Статус",
                sortable: true,
                align: "center",
                render: (_value, row) => renderStatus(row),
            }),
        ],
    });

    table.setDataSource(
        new ApiTableDataSource({
            endpoint: "/api/admin/pages",
        }),
    );

    table.addEventListener("flex-table-load-error", (event) => {
        console.error(
            "Грешка при зареждане на страниците:",
            event.detail.error,
        );

        window.flexNotify?.error("Страниците не можаха да бъдат заредени.");
    });

    table.addEventListener("flex-table-row-action", async (event) => {
        const { action, row } = event.detail;

        if (!row?.id) {
            return;
        }

        if (action === "edit") {
            visit(`/admin/pages/edit/${row.id}`);
            return;
        }

        if (action === "fields") {
            visit(`/admin/pages/${row.id}/fields`);
            return;
        }

        if (!confirmAction(action, row)) {
            return;
        }

        const endpoint = {
            toggle: `/api/admin/pages/${row.id}/toggle`,
            trash: `/api/admin/pages/${row.id}/delete`,
            restore: `/api/admin/pages/${row.id}/restore`,
            "force-delete": `/api/admin/pages/${row.id}/force-delete`,
        }[action];

        if (!endpoint) {
            return;
        }

        try {
            const data = await postJson(endpoint);

            window.flexNotify?.success(data.message);
            await table.load();
        } catch (error) {
            console.error("Грешка при действие върху страница:", error);
            window.flexNotify?.error(error.message);
        }
    });

    table.addEventListener("flex-table-bulk-action", async (event) => {
        const { action, ids } = event.detail;

        if (!Array.isArray(ids) || ids.length === 0) {
            return;
        }

        if (!confirmBulkAction(action, ids.length)) {
            return;
        }

        try {
            const data = await postJson("/api/admin/pages/bulk", {
                action,
                ids,
            });

            window.flexNotify?.success(data.message);
            table.clearSelection();
            await table.load();
        } catch (error) {
            console.error("Грешка при групово действие върху страници:", error);
            window.flexNotify?.error(error.message);
        }
    });

    table.load();
}

function column(config) {
    const result = new TableColumn(config);

    if (typeof config.render === "function") {
        result.render = config.render;
    }

    return result;
}

function pageActions(row) {
    if (row.deleted_at) {
        return [
            {
                key: "restore",
                label: "Възстановяване",
                icon: "fa-solid fa-rotate-left",
            },
            {
                key: "force-delete",
                label: "Изтриване завинаги",
                icon: "fa-solid fa-trash-can",
                destructive: true,
                separatorBefore: true,
            },
        ];
    }

    return [
        {
            key: "edit",
            label: "Редактиране",
            icon: "fa-solid fa-pen",
        },
        ...(row.is_with_page_options
            ? [
                  {
                      key: "fields",
                      label: "Допълнителни полета",
                      icon: "fa-solid fa-list-check",
                  },
              ]
            : []),
        {
            key: "toggle",
            label: row.is_active ? "Деактивиране" : "Активиране",
            icon: row.is_active ? "fa-solid fa-ban" : "fa-solid fa-check",
        },
        {
            key: "trash",
            label: "Преместване в кошчето",
            icon: "fa-solid fa-trash",
            destructive: true,
            separatorBefore: true,
        },
    ];
}

function pageBulkActions(table) {
    if ((table.filters?.status ?? "") === "deleted") {
        return [
            {
                key: "restore",
                label: "Възстановяване",
                icon: "fa-solid fa-rotate-left",
            },
            {
                key: "force-delete",
                label: "Изтриване завинаги",
                icon: "fa-solid fa-trash-can",
                destructive: true,
            },
        ];
    }

    return [
        {
            key: "activate",
            label: "Активиране",
            icon: "fa-solid fa-check",
        },
        {
            key: "deactivate",
            label: "Деактивиране",
            icon: "fa-solid fa-ban",
        },
        {
            key: "trash",
            label: "Преместване в кошчето",
            icon: "fa-solid fa-trash",
            destructive: true,
        },
    ];
}

function renderStatus(row) {
    if (row.deleted_at) {
        return html`<span class="text-xs font-medium text-red-700 dark:text-red-300"
            >В кошчето</span
        >`;
    }

    return row.is_active
        ? html`<span class="text-xs font-medium text-emerald-700 dark:text-emerald-300"
              >Активна</span
          >`
        : html`<span class="text-xs font-medium text-slate-500">Неактивна</span>`;
}

function confirmAction(action, row) {
    if (action === "trash") {
        return window.confirm(`Да преместим ли „${row.name}“ в кошчето?`);
    }

    if (action === "force-delete") {
        return window.confirm(
            `Да изтрием ли „${row.name}“ завинаги? Действието е необратимо.`,
        );
    }

    return true;
}

function confirmBulkAction(action, count) {
    if (action === "trash") {
        return window.confirm(`Да преместим ли ${count} страници в кошчето?`);
    }

    if (action === "force-delete") {
        return window.confirm(
            `Да изтрием ли ${count} страници завинаги? Действието е необратимо.`,
        );
    }

    return true;
}

async function postJson(endpoint, payload) {
    const response = await fetch(endpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-Flex-Request": "XMLHttpRequest",
        },
        body: JSON.stringify(payload),
    });
    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.message || "Груповото действие беше неуспешно.");
    }

    return data;
}

function visit(url) {
    if (window.Turbo?.visit) {
        window.Turbo.visit(url);
        return;
    }

    window.location.href = url;
}

document.addEventListener("turbo:load", initializePagesTable);

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializePagesTable, {
        once: true,
    });
} else {
    initializePagesTable();
}
