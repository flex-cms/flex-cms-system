<?php

declare(strict_types=1);

?>

<div class="space-y-5">
    <div class="flex gap-5">
        <flex-button
            href="/admin/shopping/categories/create"
            variant="primary"
            label="Нова категория"
            icon="fa-solid fa-plus"
            turbo
        ></flex-button>
    </div>

    <flex-data-table
        id="shopping-categories-table"
        row-key="id"
        empty-title="Няма категории"
        empty-description="Все още няма създадени продуктови категории."
        hoverable
        paginated
    ></flex-data-table>
</div>
