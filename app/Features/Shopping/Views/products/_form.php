<?php

declare(strict_types=1);

use Flex\Features\Shopping\Models\Product;

/** @var Product|null $product */
$product = $product ?? null;
$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isEdit = $product !== null;
$action = $isEdit
    ? '/admin/shopping/products/' . (int) $product->id . '/update'
    : '/admin/shopping/products/store';
?>

<flex-form id="shopping-product-form" action="<?= $escape($action) ?>" method="POST" mode="api" class="space-y-5">
    <flex-input
        name="name"
        label="Име"
        value="<?= $escape($product?->name ?? '') ?>"
        placeholder="Напр. Безжични слушалки"
        helper="Името се показва в продуктовите списъци и на страницата на продукта."
        required
        full-width>
    </flex-input>

    <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-5">
        <flex-input
            name="slug"
            label="Slug"
            value="<?= $escape($product?->slug ?? '') ?>"
            placeholder="bezzhichni-slushalki"
            helper="Използва се в URL адреса. Оставете празно за автоматично генериране от името."
            full-width>
        </flex-input>

        <flex-input
            name="sku"
            label="SKU"
            value="<?= $escape($product?->sku ?? '') ?>"
            placeholder="HEADPHONES-001"
            helper="Уникален вътрешен код за разпознаване и управление на продукта."
            full-width>
        </flex-input>

        <flex-dropdown
            name="status"
            label="Статус"
            value="<?= $escape($product?->status ?? 'draft') ?>"
            helper="Определя дали продуктът е видим за клиентите, остава чернова или е архивиран."
            full-width>
            <option value="draft">Чернова</option>
            <option value="published">Публикуван</option>
            <option value="archived">Архивиран</option>
        </flex-dropdown>
    </div>

    <div class="lg:col-span-2">
        <flex-input
            type="textarea"
            name="short_description"
            label="Кратко описание"
            value="<?= $escape($product?->short_description ?? '') ?>"
            placeholder="Кратко представяне на основните предимства на продукта..."
            helper="Използва се в продуктови карти, списъци и други компактни изгледи."
            rows="3"
            full-width>
        </flex-input>
    </div>

    <div class="lg:col-span-2">
        <flex-input
            type="textarea"
            name="description"
            label="Описание"
            value="<?= $escape($product?->description ?? '') ?>"
            placeholder="Опишете характеристиките, предимствата и предназначението на продукта..."
            helper="Пълното описание се показва на продуктовата страница и помага на клиента да вземе решение."
            rows="7"
            full-width>
        </flex-input>
    </div>

    <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-5">
        <flex-input
            type="number"
            step="0.01"
            min="0"
            name="price"
            label="Продажна цена"
            value="<?= $escape($product?->price ?? '0.00') ?>"
            placeholder="99.90"
            helper="Крайната цена, на която продуктът се предлага на клиентите."
            required
            full-width>
        </flex-input>

        <flex-input
            type="number"
            step="0.01"
            min="0"
            name="compare_price"
            label="Стара цена"
            value="<?= $escape($product?->compare_price ?? '') ?>"
            placeholder="129.90"
            helper="По-висока предишна цена, която се показва зачеркната при намаление."
            full-width>
        </flex-input>

        <flex-input
            type="number"
            step="0.01"
            min="0"
            name="cost_price"
            label="Доставна цена"
            value="<?= $escape($product?->cost_price ?? '') ?>"
            placeholder="65.00"
            helper="Вътрешна доставна или себестойност. Не се показва на клиентите."
            full-width>
        </flex-input>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        <flex-dropdown
            name="stock_status"
            label="Складов статус"
            value="<?= $escape($product?->stock_status ?? 'in_stock') ?>"
            helper="Показва дали продуктът е наличен, изчерпан или може да бъде поръчан предварително."
            full-width>
            <option value="in_stock">В наличност</option>
            <option value="out_of_stock">Изчерпан</option>
            <option value="backorder">Предварителна поръчка</option>
        </flex-dropdown>

        <flex-input
            type="number"
            min="0"
            name="stock_quantity"
            label="Количество"
            value="<?= (int) ($product?->stock_quantity ?? 0) ?>"
            placeholder="0"
            helper="Броят налични единици. Използва се, когато управлението на наличността е включено."
            full-width>
        </flex-input>
    </div>

    <flex-checkbox
        name="manage_stock"
        value="1"
        label="Управлявай наличността"
        helper="При включване наличното количество се следи автоматично за този продукт."
        <?= ($product?->manage_stock ?? false) ? 'checked' : '' ?>>
    </flex-checkbox>

    <flex-checkbox
        name="is_featured"
        value="1"
        label="Препоръчан продукт"
        helper="Препоръчаните продукти могат да се показват в специални секции на магазина."
        <?= ($product?->is_featured ?? false) ? 'checked' : '' ?>>
    </flex-checkbox>

    <div class="lg:col-span-2 flex items-center gap-3">
        <flex-button
            type="submit"
            variant="primary"
            label="<?= $isEdit ? 'Запази промените' : 'Създай продукт' ?>"
            icon="fa-solid fa-floppy-disk">
        </flex-button>
        <flex-button
            href="/admin/shopping/products"
            variant="secondary"
            label="Отказ"
            icon="fa-solid fa-xmark"
            turbo>
        </flex-button>
    </div>
</flex-form>
