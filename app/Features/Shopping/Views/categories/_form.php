<?php

declare(strict_types=1);

use Flex\Features\Shopping\Models\Category;

/** @var Category|null $category */
$category = $category ?? null;
$parents = $parents ?? [];

$escape = static fn (mixed $value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$isEdit = $category !== null;
$action = $isEdit
    ? '/admin/shopping/categories/' . (int) $category->id . '/update'
    : '/admin/shopping/categories/store';
?>

<flex-form
    id="shopping-category-form"
    action="<?= $escape($action) ?>"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="name"
            label="Име"
            value="<?= $escape($category?->name ?? '') ?>"
            placeholder="Напр. Компютри и лаптопи"
            helper="Името ще се показва на клиентите в менюта, категории и други части на магазина."
            full-width
        ></flex-input>

        <flex-input
            name="slug"
            label="Slug"
            value="<?= $escape($category?->slug ?? '') ?>"
            placeholder="kompyutri-i-laptopi"
            helper="Използва се в URL адреса на категорията. Оставете празно за автоматично генериране."
            full-width
        ></flex-input>

        <flex-dropdown
            name="parent_id"
            label="Родителска категория"
            value="<?= $escape($category?->parent_id ?? '') ?>"
            helper="Изберете родител, ако тази категория трябва да бъде подкатегория."
            full-width
        >
            <option value="">Без родител — основна категория</option>

            <?php foreach ($parents as $parent): ?>
                <option value="<?= (int) $parent->id ?>">
                    <?= $escape($parent->name) ?>
                </option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-input
            type="number"
            name="sort_order"
            label="Позиция"
            value="<?= (int) ($category?->sort_order ?? 0) ?>"
            placeholder="0"
            helper="Определя реда на показване. Категориите с по-малка стойност се показват по-напред."
            min="0"
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="description"
                label="Описание"
                value="<?= $escape($category?->description ?? '') ?>"
                placeholder="Опишете накратко какви продукти могат да бъдат намерени в тази категория..."
                helper="Описание на категорията, което може да бъде показвано на страницата ѝ в магазина."
                rows="5"
                full-width
            ></flex-input>
        </div>

        <flex-input
            name="image"
            label="Изображение"
            value="<?= $escape($category?->image ?? '') ?>"
            placeholder="/uploads/categories/kompyutri.webp"
            helper="Посочете изображение, което ще представя категорията в магазина."
            full-width
        ></flex-input>

        <flex-input
            name="meta_title"
            label="Meta title"
            value="<?= $escape($category?->meta_title ?? '') ?>"
            placeholder="Компютри и лаптопи | Име на магазина"
            helper="SEO заглавие за търсачките. Ако е празно, може да се използва името на категорията."
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="meta_description"
                label="Meta description"
                value="<?= $escape($category?->meta_description ?? '') ?>"
                placeholder="Разгледайте нашата селекция от компютри, лаптопи и аксесоари..."
                helper="Кратко SEO описание на категорията за резултатите в търсачките."
                rows="3"
                full-width
            ></flex-input>
        </div>

        <div class="lg:col-span-2">
            <flex-checkbox
                name="is_active"
                value="1"
                label="Активна категория"
                helper="Неактивните категории остават запазени, но не трябва да бъдат показвани на клиентите."
                <?= ($category?->is_active ?? true) ? 'checked' : '' ?>
            ></flex-checkbox>
        </div>

        <div class="lg:col-span-2 flex items-center gap-3">
            <flex-button
                type="submit"
                variant="primary"
                label="<?= $isEdit ? 'Запази промените' : 'Създай категория' ?>"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>

            <flex-button
                href="/admin/shopping/categories"
                variant="secondary"
                label="Отказ"
                icon="fa-solid fa-xmark"
                turbo
            ></flex-button>
        </div>
    </div>
</flex-form>
