import { html } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import { DateHelper } from "../../../helpers/date-helper";

const table = document.getElementById("roles-table");
const filterSearch = document.getElementById("filter-search");
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

filterSearch?.addEventListener("flex-input", (e) => {
    table?.setFilter("search", e.detail.value);
});

filterStatus?.addEventListener("flex-change", (e) => {
    table?.setFilter("status", e.detail.value);
});

filterClear?.addEventListener("flex-click", () => {
    if (filterSearch) filterSearch.value = "";
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
                <div class="flex items-center gap-2">
                    ${row.color
                        ? html`<span
                              class="h-3 w-3 rounded-full"
                              style="background-color: ${row.color}"
                          ></span>`
                        : ""}
                    <span class="font-medium">${value}</span>
                    ${row.is_default
                        ? html`<span
                              class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                              >По подразбиране</span
                          >`
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
            key: "permissions_count",
            label: "Права",
            render: (value) => html`
                <span
                    class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                >
                    ${value} ${value === 1 ? "право" : "права"}
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
                        ? '<span class="text-green-600 font-semibold">Активна</span>'
                        : '<span class="text-red-500 font-semibold">Неактивна</span>',
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
                                window.location.href = `/admin/users/roles/edit/${row.id}`;
                            },
                        },
                        {
                            key: "toggle",
                            label: row.is_active ? "Деактивирай" : "Активирай",
                            icon: row.is_active
                                ? "fa-solid fa-times"
                                : "fa-solid fa-check",
                            handler: async (row) => {
                                await axios.post("/admin/users/roles/toggle", {
                                    id: row.id,
                                });
                                table.fetchData();
                                window.notify(`Ролята ${row.name} е ${row.is_active ? 'деактивирана' : 'активирана'} успешно.`, 'success');
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
                                        `Сигурни ли сте, че искате да изтриете ролята "${row.name}"?`,
                                    )
                                ) {
                                    await axios.post(
                                        "/admin/users/roles/delete",
                                        {
                                            id: row.id,
                                        },
                                    );
                                    table.fetchData();
                                    window.notify(`Ролята ${row.name} е изтрита успешно.`, 'success');
                                }
                            },
                        },
                    ]}
                ></flex-table-actions>
            `,
        },
    ];
}
