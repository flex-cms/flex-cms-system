import { html } from "lit";

import { ApiTableDataSource, TableColumn } from "@admin-ui/components/table/index.js";
import { dateFormatter } from "../../../Settings/Resources/js/date-formatter.js";

async function initializeAuthenticationUsersTable() {
    const table = document.querySelector("#authentication-users-table");

    if (!table || table.dataset.initialized === "true") {
        return;
    }

    table.dataset.initialized = "true";

    await dateFormatter.ready();

    table.configure({
        rowKey: "id",
        hoverable: true,
        striped: false,
        paginated: true,
        searchable: true,
        searchPlaceholder: "Търсене по име, имейл или роля...",
        searchDebounce: 400,
        page: 1,
        pageSize: 25,
        pageSizeOptions: [10, 20, 25, 50, 100],
        sortBy: "fullname",
        sortDirection: "asc",

        selectable: true,

        // TODO: Когато flex-data-table поддържа selectable predicate,
        // изключете избора на текущия и на super-admin потребителя.
        bulkActions: (currentTable) => getUserBulkActions(currentTable),

        rowActions: (row) => getUserRowActions(row),

        filterDefinitions: [
            {
                key: "status",
                label: "Статус",
                type: "select",
                options: [
                    {
                        value: "",
                        label: "Всички",
                    },
                    {
                        value: "active",
                        label: "Активни",
                    },
                    {
                        value: "inactive",
                        label: "Неактивни",
                    },
                    {
                        value: "super-admin",
                        label: "Супер администратор",
                    },
                    {
                        value: "deleted",
                        label: "В кошчето",
                    },
                ],
            },
        ],

        emptyTitle: "Няма потребители",
        emptyDescription: "Няма потребители, които отговарят на текущите условия.",

        columns: [
            createTableColumn({
                key: "fullname",
                label: "Потребител",
                sortable: true,
                render: (value, row) => html`
                    <div class="space-y-0.5">
                        <div class="font-semibold text-slate-900 dark:text-white">${value}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">${row.email}</div>
                    </div>
                `,
            }),

            createTableColumn({
                key: "role_names",
                label: "Роли",
                render: (value, row) => renderRoles(value, row),
            }),

            createTableColumn({
                key: "last_login",
                label: "Последен вход",
                sortable: false,
                render: (value) => renderLastLogin(value),
            }),

            createTableColumn({
                key: "is_active",
                label: "Статус",
                sortable: true,
                align: "center",
                render: (value, row) => renderUserStatus(value, row),
            }),
        ],
    });

    table.setDataSource(
        new ApiTableDataSource({
            endpoint: "/api/admin/authentication/users",
        }),
    );

    table.addEventListener("flex-table-row-action", async (event) => {
        const { action, row } = event.detail;

        if (!row?.id) {
            return;
        }

        if (action === "edit") {
            visit(`/admin/authentication/users/${row.id}/edit`);

            return;
        }

        if (!confirmRowAction(action)) {
            return;
        }

        const endpoint = {
            toggle: `/api/admin/authentication/users/${row.id}/toggle`,
            trash: `/api/admin/authentication/users/${row.id}/delete`,
            restore: `/api/admin/authentication/users/${row.id}/restore`,
            "force-delete": `/api/admin/authentication/users/${row.id}/force-delete`,
        }[action];

        if (!endpoint) {
            return;
        }

        try {
            const data = await post(endpoint);

            notifySuccess(data.message);

            await table.load();
        } catch (error) {
            console.error("Грешка при действие върху потребител:", error);

            notifyError(error.message);
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
            const data = await post("/api/admin/authentication/users/bulk", {
                action,
                ids,
            });

            notifySuccess(data.message);

            table.clearSelection();

            await table.load();
        } catch (error) {
            console.error("Грешка при групово действие върху потребители:", error);

            notifyError(error.message);
        }
    });

    table.addEventListener("flex-table-load-error", (event) => {
        console.error("Грешка при зареждане на потребителите:", event.detail.error);

        notifyError("Потребителите не можаха да бъдат заредени.");
    });

    table.load();
}

function createTableColumn(config) {
    const column = new TableColumn(config);

    // TableColumn все още не копира render callback от config,
    // въпреки че flex-data-table го използва при рендериране.
    if (typeof config.render === "function") {
        column.render = config.render;
    }

    return column;
}

function getUserRowActions(row) {
    if (row.deleted_at) {
        const actions = [
            {
                key: "restore",
                label: "Възстановяване",
                icon: "fa-solid fa-rotate-left",
            },
        ];

        if (!row.is_super_admin && !row.is_current_user) {
            actions.push({
                key: "force-delete",
                label: "Изтриване завинаги",
                icon: "fa-solid fa-trash-can",
                destructive: true,
                separatorBefore: true,
            });
        }

        return actions;
    }

    const actions = [
        {
            key: "edit",
            label: "Редактиране",
            icon: "fa-solid fa-pen",
        },
    ];

    if (!row.is_current_user) {
        actions.push({
            key: "toggle",
            label: row.is_active ? "Деактивирай" : "Активирай",
            icon: row.is_active ? "fa-solid fa-ban" : "fa-solid fa-check",
        });
    }

    if (!row.is_super_admin && !row.is_current_user) {
        actions.push({
            key: "trash",
            label: "Премести в кошчето",
            icon: "fa-solid fa-trash",
            destructive: true,
            separatorBefore: true,
        });
    }

    return actions;
}

function getUserBulkActions(table) {
    const status = table.filters?.status ?? "";

    if (status === "deleted") {
        return [
            {
                key: "restore",
                label: "Възстанови",
                icon: "fa-solid fa-rotate-left",
            },
            {
                key: "force-delete",
                label: "Изтрий завинаги",
                icon: "fa-solid fa-trash-can",
                destructive: true,
            },
        ];
    }

    return [
        {
            key: "activate",
            label: "Активирай",
            icon: "fa-solid fa-check",
        },
        {
            key: "deactivate",
            label: "Деактивирай",
            icon: "fa-solid fa-ban",
        },
        {
            key: "trash",
            label: "Премести в кошчето",
            icon: "fa-solid fa-trash",
            destructive: true,
        },
    ];
}

function renderRoles(value, row) {
    if (row.is_super_admin) {
        return html`
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/50 dark:text-amber-300"
            >
                <i
                    class="fa-solid fa-shield-halved"
                    aria-hidden="true"
                ></i>
                Супер администратор
            </span>
        `;
    }

    if (!value) {
        return html` <span class="text-slate-400 dark:text-slate-500"> Без роля </span> `;
    }

    return html`<span>${value}</span>`;
}

function renderLastLogin(value) {
    if (!value) {
        return html` <span class="text-slate-400 dark:text-slate-500"> Няма вход </span> `;
    }

    return dateFormatter.formatDateTime(value);
}

function renderUserStatus(isActive, row) {
    if (row.deleted_at) {
        return html`
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-950/50 dark:text-red-300"
            >
                <i
                    class="fa-solid fa-trash"
                    aria-hidden="true"
                ></i>
                В кошчето
            </span>
        `;
    }

    return isActive
        ? html`
              <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
              >
                  <i
                      class="fa-solid fa-circle-check"
                      aria-hidden="true"
                  ></i>
                  Активен
              </span>
          `
        : html`
              <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
              >
                  <i
                      class="fa-solid fa-circle-xmark"
                      aria-hidden="true"
                  ></i>
                  Неактивен
              </span>
          `;
}

function confirmRowAction(action) {
    if (action === "trash") {
        return window.confirm("Сигурни ли сте, че искате да преместите потребителя в кошчето?");
    }

    if (action === "force-delete") {
        return window.confirm("Сигурни ли сте? Това действие е необратимо.");
    }

    return true;
}

function confirmBulkAction(action, count) {
    if (action === "trash") {
        return window.confirm(
            `Сигурни ли сте, че искате да преместите ${count} потребители в кошчето?`,
        );
    }

    if (action === "force-delete") {
        return window.confirm(
            `Сигурни ли сте, че искате да изтриете завинаги ${count} потребители?`,
        );
    }

    return true;
}

async function post(endpoint, payload = null) {
    const headers = {
        Accept: "application/json",
        "X-Flex-Request": "XMLHttpRequest",
    };

    const options = {
        method: "POST",
        credentials: "same-origin",
        headers,
    };

    if (payload !== null) {
        headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(payload);
    }

    const response = await fetch(endpoint, options);
    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.message || "Операцията беше неуспешна.");
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

function notifySuccess(message) {
    if (message) {
        window.flexNotify?.success(message);
    }
}

function notifyError(message) {
    window.flexNotify?.error(message || "Възникна неочаквана грешка.");
}

function initializeAuthentication() {
    initializeAuthenticationUsersTable();
}

document.addEventListener("turbo:load", initializeAuthentication);

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeAuthentication, {
        once: true,
    });
} else {
    initializeAuthentication();
}
