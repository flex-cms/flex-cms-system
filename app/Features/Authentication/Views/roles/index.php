<?php

declare(strict_types=1);
?>

<div class="space-y-5">
    <div class="flex gap-5">
        <flex-button
            href="/admin/authentication/roles/create"
            variant="primary"
            label="Нова роля"
            icon="fa-solid fa-user-shield"
            turbo
        ></flex-button>
    </div>

    <flex-data-table
        id="authentication-roles-table"
        row-key="id"
        empty-title="Няма роли"
        empty-description="Все още няма създадени роли."
        hoverable
        paginated
    ></flex-data-table>
</div>
