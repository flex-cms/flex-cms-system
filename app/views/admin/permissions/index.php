<flex-table id="permissions-table" url="/api/admin/users/permissions" page-size="10" class="space-y-5">
    <div slot="filters" class="flex items-center gap-5">
        <flex-input id="filter-search" placeholder="Търсене по име или слъг..." class="flex-1 min-w-50"></flex-input>
        <flex-select id="filter-status" placeholder="Всички статуси" class="w-48"></flex-select>
        <flex-select id="filter-module" placeholder="Всички модули" class="w-48"></flex-select>
        <flex-button id="filter-clear" label="Изчисти" variant="secondary" icon="fa-solid fa-rotate-left"></flex-button>
    </div>
</flex-table>
