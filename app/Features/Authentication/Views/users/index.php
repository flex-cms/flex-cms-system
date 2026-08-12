<?php

declare(strict_types=1);
?>

<div class="space-y-5">
    <div class="flex gap-5">
        <flex-button
            href="/admin/authentication/users/create"
            variant="primary"
            label="Нов потребител"
            icon="fa-solid fa-user-plus"
            turbo
        ></flex-button>
    </div>

    <flex-data-table
        id="authentication-users-table"
        row-key="id"
        empty-title="Няма потребители"
        empty-description="Все още няма създадени потребители."
        hoverable
        paginated
    ></flex-data-table>
</div>
