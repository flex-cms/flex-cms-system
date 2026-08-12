import { html } from "lit";

import {
    ApiTableDataSource,
    TableColumn,
} from "@admin-ui/components/table/index.js";

function initializeShoppingCategoriesTable() {
    const table = document.querySelector("#shopping-categories-table");

    if (!table || table.dataset.initialized === "true") {
        return;
    }

    table.dataset.initialized = "true";

    table.configure({
        rowKey: "id",
        hoverable: true,
        striped: false,
        paginated: true,
        searchable: true,
        searchPlaceholder: "Търсене по име или slug...",
        searchDebounce: 400,
        page: 1,
        pageSize: 25,
        pageSizeOptions: [25, 50, 100],

        selectable: true,

        bulkActions: (table) => getCategoryBulkActions(table),

        rowActions: (row) => {
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
                {
                    key: "toggle",
                    label: row.is_active ? "Деактивирай" : "Активирай",
                    icon: row.is_active
                        ? "fa-solid fa-ban"
                        : "fa-solid fa-check",
                },
                {
                    key: "trash",
                    label: "Премести в кошчето",
                    icon: "fa-solid fa-trash",
                    destructive: true,
                    separatorBefore: true,
                },
            ];
        },

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
                        value: "deleted",
                        label: "В кошчето",
                    },
                ],
            },
        ],

        emptyTitle: "Няма категории",
        emptyDescription:
            "Няма категории, които отговарят на текущите условия.",

        columns: [
            new TableColumn({
                key: "name",
                label: "Име",
                sortable: true,
            }),

            new TableColumn({
                key: "slug",
                label: "Slug",
                sortable: true,
            }),

            new TableColumn({
                key: "parent_name",
                label: "Родител",

                render: (value) =>
                    value
                        ? html`<span>${value}</span>`
                        : html`
                              <span class="text-slate-400 dark:text-slate-500">
                                  Основна категория
                              </span>
                          `,
            }),

            new TableColumn({
                key: "sort_order",
                label: "Позиция",
                sortable: true,
                align: "center",
            }),

            new TableColumn({
                key: "is_active",
                label: "Статус",
                sortable: true,
                align: "center",

                render: (value) => renderCategoryStatus(Boolean(value)),
            }),
        ],
    });

    table.setDataSource(
        new ApiTableDataSource({
            endpoint: "/api/admin/shopping/categories",
        }),
    );

    table.addEventListener("flex-table-row-action", async (event) => {
        const { action, row } = event.detail;

        if (!row?.id) {
            return;
        }

        if (action === "edit") {
            if (window.Turbo?.visit) {
                window.Turbo.visit(`/admin/shopping/categories/${row.id}/edit`);
            } else {
                window.location.href = `/admin/shopping/categories/${row.id}/edit`;
            }

            return;
        }

        if (
            (action === "trash" || action === "force-delete") &&
            !window.confirm(
                action === "force-delete"
                    ? "Сигурни ли сте? Това действие е необратимо."
                    : "Сигурни ли сте, че искате да преместите категорията в кошчето?",
            )
        ) {
            return;
        }

        const endpoint = {
            toggle: `/api/admin/shopping/categories/${row.id}/toggle`,
            trash: `/api/admin/shopping/categories/${row.id}/delete`,
            restore: `/api/admin/shopping/categories/${row.id}/restore`,
            "force-delete": `/api/admin/shopping/categories/${row.id}/force-delete`,
        }[action];

        if (!endpoint) {
            return;
        }

        try {
            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-Flex-Request": "XMLHttpRequest",
                },
            });

            const data = await response.json();

            if (!response.ok || data.success === false) {
                throw new Error(data.message || "Операцията беше неуспешна.");
            }

            if (typeof window.notify === "function" && data.message) {
                window.notify(data.message, "success");
            }

            await table.load();
        } catch (error) {
            console.error("Грешка при действие върху категория:", error);

            if (typeof window.notify === "function") {
                window.notify(error.message, "error");
            }
        }
    });

    table.addEventListener("flex-table-bulk-action", async (event) => {
        const { action, ids } = event.detail;

        if (!Array.isArray(ids) || ids.length === 0) {
            return;
        }

        if (
            action === "trash" &&
            !window.confirm(
                `Сигурни ли сте, че искате да преместите ${ids.length} категории в кошчето?`,
            )
        ) {
            return;
        }

        try {
            const response = await fetch(
                "/api/admin/shopping/categories/bulk",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Flex-Request": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        action,
                        ids,
                    }),
                },
            );

            const data = await response.json();

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.message || "Груповата операция беше неуспешна.",
                );
            }

            if (typeof window.notify === "function" && data.message) {
                window.notify(data.message, "success");
            }

            table.clearSelection();

            await table.load();
        } catch (error) {
            console.error("Грешка при групова операция:", error);

            if (typeof window.notify === "function") {
                window.notify(error.message, "error");
            }
        }
    });

    table.addEventListener("flex-table-load-error", (event) => {
        console.error(
            "Грешка при зареждане на категориите:",
            event.detail.error,
        );
    });

    table.load();
}

function renderCategoryStatus(isActive) {
    return isActive
        ? html`
              <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
              >
                  <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                  Активна
              </span>
          `
        : html`
              <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
              >
                  <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                  Неактивна
              </span>
          `;
}

function getCategoryBulkActions(table) {
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

function initializeShopping() {
    initializeShoppingCategoriesTable();
    initializeShoppingProductsTable();
}

function initializeShoppingProductsTable() {
    const table = document.querySelector("#shopping-products-table");

    if (!table || table.dataset.initialized === "true") {
        return;
    }

    table.dataset.initialized = "true";
    table.configure({
        rowKey: "id",
        hoverable: true,
        paginated: true,
        searchable: true,
        searchPlaceholder: "Търсене по име, slug, SKU или категория...",
        searchDebounce: 400,
        page: 1,
        pageSize: 25,
        pageSizeOptions: [25, 50, 100],
        selectable: true,
        bulkActions: (currentTable) => getProductBulkActions(currentTable),
        rowActions: (row) =>
            row.deleted_at
                ? [
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
                  ]
                : [
                      {
                          key: "edit",
                          label: "Редактиране",
                          icon: "fa-solid fa-pen",
                      },
                      {
                          key: "toggle",
                          label:
                              row.status === "published"
                                  ? "Върни като чернова"
                                  : "Публикувай",
                          icon:
                              row.status === "published"
                                  ? "fa-solid fa-file"
                                  : "fa-solid fa-check",
                      },
                      {
                          key: "trash",
                          label: "Премести в кошчето",
                          icon: "fa-solid fa-trash",
                          destructive: true,
                          separatorBefore: true,
                      },
                  ],
        filterDefinitions: [
            {
                key: "status",
                label: "Статус",
                type: "select",
                options: [
                    { value: "", label: "Всички" },
                    { value: "published", label: "Публикувани" },
                    { value: "draft", label: "Чернови" },
                    { value: "archived", label: "Архивирани" },
                    { value: "deleted", label: "В кошчето" },
                ],
            },
            {
                key: "stock",
                label: "Наличност",
                type: "select",
                options: [
                    { value: "", label: "Всички" },
                    { value: "in_stock", label: "В наличност" },
                    { value: "out_of_stock", label: "Изчерпани" },
                    { value: "backorder", label: "Предварителна поръчка" },
                ],
            },
        ],
        emptyTitle: "Няма продукти",
        emptyDescription: "Няма продукти, които отговарят на текущите условия.",
        columns: [
            productColumn({
                key: "name",
                label: "Продукт",
                sortable: true,
                render: (value, row) => html`
                    <div class="flex items-center gap-3">
                        ${row.primary_image
                            ? html`<img
                                  class="h-10 w-10 rounded object-cover"
                                  src=${row.primary_image}
                                  alt=""
                              />`
                            : html`<span
                                  class="flex h-10 w-10 items-center justify-center rounded bg-slate-100 dark:bg-slate-800"
                                  ><i class="fa-solid fa-box"></i
                              ></span>`}
                        <div>
                            <div class="font-medium">${value}</div>
                            <div class="text-xs text-slate-500">
                                ${row.sku || "Без SKU"}
                            </div>
                        </div>
                    </div>
                `,
            }),
            productColumn({ key: "categories", label: "Категории" }),
            productColumn({
                key: "price",
                label: "Цена",
                sortable: true,
                align: "right",
                render: (value) => `${Number(value).toFixed(2)} лв.`,
            }),
            productColumn({
                key: "stock_quantity",
                label: "Наличност",
                sortable: true,
                align: "center",
                render: (value, row) =>
                    row.manage_stock
                        ? `${value} бр.`
                        : stockLabel(row.stock_status),
            }),
            productColumn({
                key: "status",
                label: "Статус",
                sortable: true,
                align: "center",
                render: (value) => renderProductStatus(value),
            }),
        ],
    });

    table.setDataSource(
        new ApiTableDataSource({ endpoint: "/api/admin/shopping/products" }),
    );

    table.addEventListener("flex-table-row-action", async (event) => {
        const { action, row } = event.detail;

        if (!row?.id) return;

        if (action === "edit") {
            visit(`/admin/shopping/products/${row.id}/edit`);
            return;
        }

        if (
            ["trash", "force-delete"].includes(action) &&
            !window.confirm(
                action === "force-delete"
                    ? "Сигурни ли сте? Това действие е необратимо."
                    : "Да преместя ли продукта в кошчето?",
            )
        )
            return;

        const endpoint = {
            toggle: `/api/admin/shopping/products/${row.id}/toggle`,
            trash: `/api/admin/shopping/products/${row.id}/delete`,
            restore: `/api/admin/shopping/products/${row.id}/restore`,
            "force-delete": `/api/admin/shopping/products/${row.id}/force-delete`,
        }[action];

        if (endpoint) await runProductAction(table, endpoint);
    });

    table.addEventListener("flex-table-bulk-action", async (event) => {
        const { action, ids } = event.detail;
        if (!Array.isArray(ids) || ids.length === 0) return;
        if (
            ["trash", "force-delete"].includes(action) &&
            !window.confirm(`Да обработя ли избраните ${ids.length} продукта?`)
        )
            return;

        await runProductAction(table, "/api/admin/shopping/products/bulk", {
            action,
            ids,
        });
        table.clearSelection();
    });

    table.load();
}

function productColumn(options) {
    const column = new TableColumn(options);
    if (options.render) column.render = options.render;
    return column;
}

function renderProductStatus(status) {
    const labels = {
        published: "Публикуван",
        draft: "Чернова",
        archived: "Архивиран",
    };
    const classes =
        status === "published"
            ? "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
            : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300";
    return html`<span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${classes}"
        >${labels[status] || status}</span
    >`;
}

function stockLabel(status) {
    return (
        {
            in_stock: "В наличност",
            out_of_stock: "Изчерпан",
            backorder: "Предварителна",
        }[status] || "—"
    );
}

function getProductBulkActions(table) {
    if ((table.filters?.status ?? "") === "deleted") {
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
        { key: "publish", label: "Публикувай", icon: "fa-solid fa-check" },
        { key: "draft", label: "Направи чернова", icon: "fa-solid fa-file" },
        { key: "archive", label: "Архивирай", icon: "fa-solid fa-box-archive" },
        {
            key: "trash",
            label: "Премести в кошчето",
            icon: "fa-solid fa-trash",
            destructive: true,
        },
    ];
}

async function runProductAction(table, endpoint, body = null) {
    try {
        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                ...(body ? { "Content-Type": "application/json" } : {}),
                Accept: "application/json",
                "X-Flex-Request": "XMLHttpRequest",
            },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await response.json();
        if (!response.ok || data.success === false)
            throw new Error(data.message || "Операцията беше неуспешна.");
        notifyProduct(data.message, "success");
        await table.load();
    } catch (error) {
        console.error("Грешка при действие върху продукт:", error);
        notifyProduct(error.message, "error");
    }
}

function notifyProduct(message, type) {
    if (!message) return;
    if (window.flexNotify?.[type]) window.flexNotify[type](message);
    else if (typeof window.notify === "function") window.notify(message, type);
}

function visit(url) {
    if (window.Turbo?.visit) window.Turbo.visit(url);
    else window.location.href = url;
}

document.addEventListener("turbo:load", initializeShopping);

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeShopping, {
        once: true,
    });
} else {
    initializeShopping();
}
