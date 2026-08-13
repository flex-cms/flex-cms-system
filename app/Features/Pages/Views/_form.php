<?php

declare(strict_types=1);

use Flex\Features\Pages\Data\PageTreeItem;
use Flex\Features\Pages\Models\Page;

/** @var Page|null $page */
$page = isset($page) && $page instanceof Page ? $page : null;
/** @var list<PageTreeItem> $parentPages */
$parentPages = isset($parentPages) && is_array($parentPages) ? $parentPages : [];
/** @var array<string, mixed> $options */
$options = isset($options) && is_array($options) ? $options : [];
/** @var array<string, string> $templates */
$templates = isset($templates) && is_array($templates) ? $templates : [];
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isEdit = $page !== null;
$action = $isEdit ? '/admin/pages/update/' . (int) $page->id : '/admin/pages/store';

?>

<flex-form
    id="pages-form"
    action="<?= $escape($action) ?>"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="name"
            label="Име на страницата"
            value="<?= $escape($page?->name ?? '') ?>"
            placeholder="Напр. За нас"
            helper="Името, което ще се показва в администрацията и навигацията."
            maxlength="255"
            icon="fa-solid fa-file-lines"
            required
            full-width
        ></flex-input>

        <flex-input
            name="slug"
            label="URL slug"
            value="<?= $escape($page?->slug ?? '') ?>"
            placeholder="za-nas"
            helper="Оставете празно за автоматично генериране от името."
            maxlength="255"
            icon="fa-solid fa-link"
            full-width
        ></flex-input>

        <flex-dropdown
            name="parent_id"
            label="Родителска страница"
            value="<?= $escape($page?->parent_id ?? '') ?>"
            placeholder="Няма — основна страница"
            helper="Изберете родител, за да поставите страницата в йерархията."
            full-width
        >
            <option value="">Няма — основна страница</option>
            <?php foreach ($parentPages as $item): ?>
                <?php if (!$item instanceof PageTreeItem || (int) $item->page->id === (int) ($page?->id ?? 0)) { continue; } ?>
                <option
                    value="<?= (int) $item->page->id ?>"
                    <?= (int) ($page?->parent_id ?? 0) === (int) $item->page->id ? 'selected' : '' ?>
                ><?= $escape($item->displayName()) ?></option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-input
            type="number"
            name="position"
            label="Позиция"
            value="<?= (int) ($page?->position ?? 0) ?>"
            min="0"
            step="1"
            helper="Определя реда на страницата спрямо останалите страници."
            icon="fa-solid fa-arrow-down-1-9"
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-checkbox
                name="is_active"
                value="1"
                label="Активна страница"
                helper="Неактивните страници не трябва да бъдат достъпни в публичната част."
                <?= ($page?->is_active ?? true) ? 'checked' : '' ?>
            ></flex-checkbox>
        </div>

        <div class="lg:col-span-2 mt-2 border-t border-slate-200 pt-5 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Съдържание и представяне</h2>
        </div>

        <flex-dropdown
            name="options[page_template]"
            label="Шаблон"
            value="<?= $escape($options['page_template'] ?? '') ?>"
            placeholder="Без шаблон"
            helper="Шаблонът определя начина, по който страницата ще бъде визуализирана."
            full-width
        >
            <option value="">Без шаблон</option>
            <?php foreach ($templates as $template => $label): ?>
                <option
                    value="<?= $escape($template) ?>"
                    <?= ($options['page_template'] ?? '') === $template ? 'selected' : '' ?>
                ><?= $escape($label) ?></option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-input
            name="options[page_options_key]"
            label="Ключ за допълнителни полета"
            value="<?= $escape($options['page_options_key'] ?? '') ?>"
            placeholder="напр. landing"
            helper="Свързва страницата с предварително дефинирана група от допълнителни полета."
            icon="fa-solid fa-key"
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="options[excerpt]"
                label="Резюме"
                value="<?= $escape($options['excerpt'] ?? '') ?>"
                placeholder="Кратко описание на страницата..."
                helper="Използва се в списъци, карти и метаданни, когато шаблонът го поддържа."
                rows="4"
                icon="fa-solid fa-align-left"
                full-width
            ></flex-input>
        </div>

        <flex-checkbox
            name="options[use_full_slug]"
            value="1"
            label="Използвай пълния йерархичен път"
            helper="Включва slug адресите на родителските страници в публичния URL."
            <?= ($options['use_full_slug'] ?? true) ? 'checked' : '' ?>
        ></flex-checkbox>

        <flex-checkbox
            name="options[is_with_page_options]"
            value="1"
            label="Активирай допълнителни полета"
            helper="Позволява използването на конфигурираните допълнителни полета за страницата."
            <?= ($options['is_with_page_options'] ?? false) ? 'checked' : '' ?>
        ></flex-checkbox>

        <?php /* TODO: Добавяне на flex-image-upload компонент за featured, tablet и mobile изображения. */ ?>

        <div class="lg:col-span-2 flex items-center gap-3">
            <flex-button
                type="submit"
                variant="primary"
                label="<?= $isEdit ? 'Запази промените' : 'Създай страница' ?>"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>

            <flex-button
                href="/admin/pages"
                variant="secondary"
                label="Отказ"
                icon="fa-solid fa-xmark"
                turbo
            ></flex-button>
        </div>
    </div>
</flex-form>
