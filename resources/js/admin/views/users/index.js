import { html } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import { DateHelper } from "../../../helpers/date-helper";

const table = document.getElementById("users-table");
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

table.columns = [
    {
        key: "id",
        label: "ID",
        sortable: true,
        width: "80px",
    },
    {
        key: "fullname",
        label: "Име",
        sortable: true,
    },
    {
        key: "email",
        label: "Имейл",
        sortable: true,
    },
    {
        key: "roles",
        label: "Роли",
        render: (roles) => (Array.isArray(roles) ? roles.join(", ") : "—"),
    },
    {
        key: "is_active",
        label: "Статус",
        render: (value) =>
            unsafeHTML(
                value
                    ? '<span class="text-green-600 font-semibold">Активен</span>'
                    : '<span class="text-red-500 font-semibold">Неактивен</span>',
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
                            window.location.href = `/admin/users/edit/${row.id}`;
                        },
                    },
                    {
                        key: "toggle",
                        label: row.is_active ? "Деактивирай" : "Активирай",
                        icon: row.is_active
                            ? "fa-solid fa-times"
                            : "fa-solid fa-check",
                        handler: async (row) => {
                            await axios.post("/admin/users/toggle", {
                                id: row.id,
                            });
                            table.fetchData();
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
                                    `Сигурни ли сте, че искате да изтриете ${row.fullname}?`,
                                )
                            ) {
                                await axios.post("/admin/users/delete", {
                                    id: row.id,
                                });
                                table.fetchData();
                            }
                        },
                    },
                ]}
            ></flex-table-actions>
        `,
    },
];