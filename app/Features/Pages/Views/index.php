<?php

declare(strict_types=1);
?>

<div class="space-y-5">
    <div class="flex gap-5">
        <flex-button
            href="/admin/pages/create"
            variant="primary"
            label="Нова страница"
            icon="fa-solid fa-file-circle-plus"
            turbo
        ></flex-button>
    </div>

    <flex-data-table
        id="pages-table"
        row-key="id"
        empty-title="Няма страници"
        empty-description="Все още няма създадени страници или няма резултати за избраните филтри."
        hoverable
        paginated
        searchable
    ></flex-data-table>
</div>
