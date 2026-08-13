<?php

declare(strict_types=1);

use Flex\Features\Pages\Data\PageFieldType;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageField;

/** @var Page $page */
/** @var PageField|null $field */
$field = isset($field) && $field instanceof PageField ? $field : null;
$types = isset($types) && is_array($types)
    ? $types
    : PageFieldType::options();
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
$isEdit = $field !== null;
$action = $isEdit
    ? '/admin/pages/' . (int) $page->id . '/fields/' . (int) $field->id . '/update'
    : '/admin/pages/' . (int) $page->id . '/fields/store';
$selectedType = $field?->type instanceof PageFieldType
    ? $field->type->value
    : (string) ($field?->type ?? PageFieldType::Text->value);
?>

<flex-form
    id="page-field-form"
    action="<?= $escape($action) ?>"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-dropdown
            name="type"
            label="Тип на полето"
            value="<?= $escape($selectedType) ?>"
            helper="Определя как ще се въвежда и визуализира стойността."
            required
            full-width
        >
            <?php foreach ($types as $value => $label): ?>
                <option
                    value="<?= $escape($value) ?>"
                    <?= $selectedType === (string) $value ? 'selected' : '' ?>
                ><?= $escape($label) ?></option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-input
            name="label"
            label="Етикет"
            value="<?= $escape($field?->label ?? '') ?>"
            placeholder="Напр. Основно заглавие"
            helper="Името на полето, което ще вижда редакторът."
            maxlength="255"
            icon="fa-solid fa-tag"
            required
            full-width
        ></flex-input>

        <flex-input
            name="key"
            label="Ключ"
            value="<?= $escape($field?->field_key ?? '') ?>"
            placeholder="напр. main_title"
            helper="Уникален машинен ключ за това поле в рамките на страницата."
            maxlength="100"
            icon="fa-solid fa-key"
            required
            full-width
        ></flex-input>

        <flex-input
            name="group"
            label="Група"
            value="<?= $escape($field?->field_group ?? 'general') ?>"
            placeholder="general"
            helper="Полетата от една група се показват заедно."
            maxlength="100"
            icon="fa-solid fa-layer-group"
            required
            full-width
        ></flex-input>

        <flex-input
            type="number"
            name="order"
            label="Ред"
            value="<?= (int) ($field?->position ?? 0) ?>"
            min="0"
            step="1"
            helper="Определя позицията на полето в неговата група."
            icon="fa-solid fa-arrow-down-1-9"
            required
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="hint"
                label="Подсказка"
                value="<?= $escape($field?->hint ?? '') ?>"
                placeholder="Кратко пояснение какво съдържание се очаква..."
                helper="Описателен текст, който ще се показва до полето."
                rows="4"
                icon="fa-solid fa-circle-info"
                full-width
            ></flex-input>
        </div>

        <?php /* TODO: Настройки, специфични за избрания тип поле. */ ?>

        <div class="lg:col-span-2 flex flex-wrap items-center gap-3">
            <flex-button
                type="submit"
                variant="primary"
                label="<?= $isEdit ? 'Запази промените' : 'Създай поле' ?>"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>

            <flex-button
                href="/admin/pages/<?= (int) $page->id ?>/fields"
                variant="secondary"
                label="Отказ"
                icon="fa-solid fa-xmark"
                turbo
            ></flex-button>
        </div>
    </div>
</flex-form>
