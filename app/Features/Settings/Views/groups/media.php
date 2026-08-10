<?php

declare(strict_types=1);

use Flex\Features\Settings\Support\SettingsView as View;

$values = $values ?? [];
?>

<flex-form
    id="settings-media-form"
    action="/admin/settings/media/update"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            type="number"
            name="settings[media_max_size]"
            label="Максимален размер на файл"
            value="<?= View::escape(View::value($values, 'media_max_size', 5)) ?>"
            min="1"
            helper="Максималният разрешен размер на един качван файл в MB."
            icon="fa-solid fa-weight-hanging"
            full-width
        ></flex-input>

        <flex-input
            name="settings[media_allowed_extensions]"
            label="Разрешени разширения"
            value="<?= View::escape(View::value(
                $values,
                'media_allowed_extensions',
                'jpg,png,webp'
            )) ?>"
            placeholder="jpg,png,webp,pdf"
            helper="Списък с разрешените файлови разширения, разделени със запетая."
            icon="fa-solid fa-file"
            full-width
        ></flex-input>

        <flex-checkbox
            name="settings[media_use_date_folders]"
            value="1"
            label="Папки по дата"
            helper="Организира качените файлове автоматично в директории според датата на качване."
            <?= View::checked(
                $values,
                'media_use_date_folders',
                true
            ) ?>
        ></flex-checkbox>

        <flex-checkbox
            name="settings[media_keep_original_name]"
            value="1"
            label="Запазване на оригиналното име"
            helper="Запазва оригиналното име на качения файл вместо автоматично генерирано име."
            <?= View::checked(
                $values,
                'media_keep_original_name'
            ) ?>
        ></flex-checkbox>

        <div class="lg:col-span-2">
            <flex-button
                type="submit"
                variant="primary"
                label="Запази"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>
        </div>
    </div>
</flex-form>
