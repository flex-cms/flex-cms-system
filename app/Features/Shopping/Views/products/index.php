<?php

declare(strict_types=1);
?>

<div class="space-y-5">
    <flex-button
        href="/admin/shopping/products/create"
        variant="primary"
        label="Нов продукт"
        icon="fa-solid fa-plus"
        turbo
    ></flex-button>

    <flex-data-table
        id="shopping-products-table"
        row-key="id"
        empty-title="Няма продукти"
        empty-description="Все още няма създадени продукти."
        hoverable
        paginated
    ></flex-data-table>
</div>
