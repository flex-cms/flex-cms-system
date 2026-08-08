import { html } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import { DateHelper } from "../../../helpers/date-helper";

const table = document.getElementById("permissions-table");
const filterSearch = document.getElementById("filter-search");
const filterModule = document.getElementById("filter-module");
const filterStatus = document.getElementById("filter-status");
const filterClear = document.getElementById("filter-clear");

if (filterStatus) {
    filterStatus.options = [
        { value: "", label: "Всички статуси" },
        { value: "active", label: "Активни" },
        { value: "inactive", label: "Неактивни" },
        { value: "deleted", label: "Изтрити" },
    ];
}

table?.addEventListener("flex-table-fetch-success", (e) => {
    if (
        e.detail?.data.data.modules &&
        filterModule &&
        (!filterModule.options || filterModule.options.length <= 1)
    ) {
        filterModule.options = [
            { value: "", label: "Всички модули" },
            ...e.detail.data.data.modules.map((m) => ({ value: m, label: m })),
        ];
    }
});

filterSearch?.addEventListener("flex-input", (e) => {
    table?.setFilter("search", e.detail.value);
});

filterModule?.addEventListener("flex-change", (e) => {
    table?.setFilter("module", e.detail.value);
});

filterStatus?.addEventListener("flex-change", (e) => {
    table?.setFilter("status", e.detail.value);
});

filterClear?.addEventListener("flex-click", () => {
    if (filterSearch) filterSearch.value = "";
    if (filterModule) filterModule.value = "";
    if (filterStatus) filterStatus.value = "";

    table?.clearFilters();
});

if (table) {
    table.columns = [
        {
            key: "id",
            label: "ID",
            sortable: true,
            width: "80px",
        },
        {
            key: "name",
            label: "Име",
            sortable: true,
            render: (value, row) => html`
                <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        ${value}
                    </div>
                    ${row.description
                        ? html`<div
                              class="text-xs text-gray-500 dark:text-gray-400"
                          >
                              ${row.description}
                          </div>`
                        : ""}
                </div>
            `,
        },
        {
            key: "slug",
            label: "Слъг",
            sortable: true,
            render: (value) => html`
                <code
                    class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                    ${value}
                </code>
            `,
        },
        {
            key: "module",
            label: "Модул",
            sortable: true,
            render: (value) => html`
                <span
                    class="rounded-md bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300"
                >
                    ${value || "Общ"}
                </span>
            `,
        },
        {
            key: "roles_count",
            label: "Роли",
            render: (value) => html`
                <span
                    class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                >
                    ${value} ${value === 1 ? "роля" : "роли"}
                </span>
            `,
        },
        {
            key: "is_active",
            label: "Статус",
            sortable: true,
            render: (value) =>
                unsafeHTML(
                    value
                        ? '<span class="text-green-600 font-semibold">Активно</span>'
                        : '<span class="text-red-500 font-semibold">Неактивно</span>',
                ),
        },
        {
            key: "created_at",
            label: "Дата на създаване",
            sortable: true,
            type: "date",
            render: (value) => html`
                <span title="${DateHelper.format(value, true)}">
                    ${DateHelper.fromNow(value)}
                </span>
            `,
        },
        {
            key: "actions",
            label: "Действия",
            headerClass: "capitalize text-right px-5 py-3 w-12",
            cellClass: "px-5 py-3.5 text-right",
            render: (_, row) => html`
                <flex-table-actions
                    .row=${row}
                    .actions=${[
                        {
                            key: "edit",
                            label: "Редактиране",
                            icon: "fa-solid fa-pen-to-square",
                            handler: (row) => {
                                window.location.href = `/admin/users/permissions/edit/${row.id}`;
                            },
                        },
                        {
                            key: "toggle",
                            label: row.is_active ? "Деактивирай" : "Активирай",
                            icon: row.is_active
                                ? "fa-solid fa-times"
                                : "fa-solid fa-check",
                            handler: async (row) => {
                                await axios.post(
                                    "/admin/users/permissions/toggle",
                                    {
                                        id: row.id,
                                    },
                                );
                                table.fetchData();
                                window.notify(
                                    `Разрешението ${row.name} е ${row.is_active ? "деактивирано" : "активирано"} успешно.`,
                                    "success",
                                );
                            },
                        },
                        { divider: true },
                        {
                            key: "delete",
                            label: "Изтриване",
                            icon: "fa-solid fa-trash",
                            danger: true,
                            handler: async (row) => {
                                if (
                                    confirm(
                                        `Сигурни ли сте, че искате да изтриете разрешението "${row.name}"?`,
                                    )
                                ) {
                                    await axios.post(
                                        "/admin/users/permissions/delete",
                                        {
                                            id: row.id,
                                        },
                                    );
                                    table.fetchData();
                                    window.notify(
                                        `Разрешението ${row.name} е изтрито успешно.`,
                                        "success",
                                    );
                                }
                            },
                        },
                    ]}
                ></flex-table-actions>
            `,
        },
    ];
}
