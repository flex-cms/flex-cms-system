<flex-table id="users-table" url="/api/admin/users" page-size="10" class="space-y-5">
    <div slot="filters" class="flex items-center gap-5">
        <flex-input id="filter-search" placeholder="Търсене по име или имейл..." class="flex-1 min-w-50"></flex-input>
        <flex-select id="filter-status" placeholder="Всички статуси" class="w-48"></flex-select>
        <flex-button id="filter-clear" label="Изчисти" variant="secondary" icon="fa-solid fa-rotate-left"></flex-button>
    </div>
</flex-table>