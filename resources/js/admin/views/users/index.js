import { html } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import { DateHelper } from "@/helpers/date-helper.js";
import { createResourceActions } from "@/helpers/create-resource-actions";

const $table = $("#users-table");
const $filterSearch = $("#filter-search");
const $filterStatus = $("#filter-status");
const $filterClear = $("#filter-clear");

const table = $table[0];

if ($filterStatus.length) {
    $filterStatus.prop("options", [
        {
            value: "",
            label: "Всички статуси",
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
            label: "Изтрити",
        },
    ]);
}

$filterSearch.on("flex-input", function (event) {
    const detail = event.originalEvent?.detail ?? event.detail;

    table?.setFilter("search", detail.value);
});

$filterStatus.on("flex-change", function (event) {
    const detail = event.originalEvent?.detail ?? event.detail;

    table?.setFilter("status", detail.value);
});

$filterClear.on("flex-click", function () {
    $filterSearch.prop("value", "");
    $filterStatus.prop("value", "");

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
                    .actions=${createResourceActions({
                        row,
                        table,

                        editUrl: (row) => `/admin/users/edit/${row.id}`,

                        toggleEndpoint: "/admin/users/toggle",
                        trashEndpoint: "/admin/users/delete",
                        restoreEndpoint: "/admin/users/restore",
                        forceDeleteEndpoint: "/admin/users/force-delete",

                        getName: (row) => row.fullname,

                        toggleSuccessMessage: (row, wasActive) =>
                            `Потребителят ${row.fullname} е ${
                                wasActive ? "деактивиран" : "активиран"
                            } успешно.`,

                        trashSuccessMessage: (row) =>
                            `Потребителят ${row.fullname} е преместен в кошчето успешно.`,

                        restoreSuccessMessage: (row) =>
                            `Потребителят ${row.fullname} е възстановен успешно.`,

                        forceDeleteSuccessMessage: (row) =>
                            `Потребителят ${row.fullname} е изтрит завинаги.`,
                    })}
                ></flex-table-actions>
            `,
        },
    ];
}
