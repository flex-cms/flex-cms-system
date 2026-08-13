import { html } from "lit";

import {
    ApiTableDataSource,
    TableColumn,
} from "@admin-ui/components/table/index.js";

function initializePageFieldsTable() {
    const table = document.querySelector("#page-fields-table");

    if (!table || table.dataset.initialized === "true") {
        return;
    }

    const pageId = Number(table.dataset.pageId);

    if (!Number.isInteger(pageId) || pageId < 1) {
        return;
    }

    table.dataset.initialized = "true";
    table.configure({
        rowKey: "id",
        hoverable: true,
        paginated: true,
        searchable: true,
        searchPlaceholder: "Търсене по етикет, ключ или група...",
        searchDebounce: 300,
        page: 1,
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        sortBy: "position",
        sortDirection: "asc",
        rowActions: () => [
            { key: "edit", label: "Редактиране", icon: "fa-solid fa-pen" },
            {
                key: "delete",
                label: "Изтриване",
                icon: "fa-solid fa-trash",
                destructive: true,
                separatorBefore: true,
            },
        ],
        columns: [
            column({
                key: "label",
                label: "Поле",
                sortable: true,
                render: (value, row) => html`
                    <div class="space-y-0.5">
                        <button
                            type="button"
                            class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300"
                            @click=${() => visit(`/admin/pages/${pageId}/fields/${row.id}/edit`)}
                        >${value}</button>
                        <div class="text-xs text-slate-500 dark:text-slate-400">${row.key}</div>
                    </div>
                `,
            }),
            column({ key: "type_label", label: "Тип" }),
            column({ key: "group", label: "Група", sortable: true }),
            column({ key: "order", label: "Ред", sortable: true, align: "center" }),
        ],
    });

    table.setDataSource(new ApiTableDataSource({
        endpoint: `/api/admin/pages/${pageId}/fields`,
    }));

    table.addEventListener("flex-table-row-action", async (event) => {
        const { action, row } = event.detail;

        if (!row?.id) return;
        if (action === "edit") {
            visit(`/admin/pages/${pageId}/fields/${row.id}/edit`);
            return;
        }
        if (action !== "delete" || !window.confirm(`Да изтрием ли полето „${row.label}“?`)) return;

        try {
            const response = await fetch(`/api/admin/pages/${pageId}/fields/${row.id}/delete`, {
                method: "POST",
                credentials: "same-origin",
                headers: { Accept: "application/json", "X-Flex-Request": "XMLHttpRequest" },
            });
            const data = await response.json();

            if (!response.ok || data.success === false) throw new Error(data.message || "Полето не можа да бъде изтрито.");
            window.flexNotify?.success(data.message);
            await table.load();
        } catch (error) {
            console.error("Грешка при изтриване на поле:", error);
            window.flexNotify?.error(error.message);
        }
    });

    table.addEventListener("flex-table-load-error", () => {
        window.flexNotify?.error("Полетата не можаха да бъдат заредени.");
    });

    table.load();
}

function column(config) {
    const result = new TableColumn(config);
    if (typeof config.render === "function") result.render = config.render;
    return result;
}

function visit(url) {
    if (window.Turbo?.visit) window.Turbo.visit(url);
    else window.location.href = url;
}

document.addEventListener("turbo:load", initializePageFieldsTable);
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializePageFieldsTable, { once: true });
} else {
    initializePageFieldsTable();
}
