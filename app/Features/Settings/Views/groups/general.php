<?php

declare(strict_types=1);

use Flex\Features\Settings\Support\SettingsView as View;

$values = $values ?? [];
$languages = $languages ?? [];
$timezones = $timezones ?? [];
$dateFormats = $dateFormats ?? [];
?>

<flex-form
    id="settings-general-form"
    action="/admin/settings/general/update"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="settings[site_name]"
            label="Име на сайта"
            value="<?= View::escape(
                View::value(
                    $values,
                    'site_name',
                    'Flex CMS'
                )
            ) ?>"
            icon="fa-solid fa-globe"
            helper="Името, което ще се използва в административния панел и публичната част на сайта."
            required
            full-width
        ></flex-input>

        <flex-input
            type="email"
            name="settings[admin_email]"
            label="Административен имейл"
            value="<?= View::escape(
                View::value(
                    $values,
                    'admin_email',
                    ''
                )
            ) ?>"
            icon="fa-solid fa-envelope"
            helper="Основният административен имейл на системата. Полето не може да се редактира от тази страница."
            readonly
            full-width
        ></flex-input>

        <flex-input
            type="url"
            name="settings[site_url]"
            label="URL адрес"
            value="<?= View::escape(
                View::value(
                    $values,
                    'site_url',
                    ''
                )
            ) ?>"
            placeholder="https://example.com"
            icon="fa-solid fa-link"
            helper="Основният публичен адрес, на който е достъпен сайтът."
            full-width
        ></flex-input>

        <flex-dropdown
            name="settings[timezone]"
            label="Часова зона"
            value="<?= View::escape(
                View::value(
                    $values,
                    'timezone',
                    'Europe/Sofia'
                )
            ) ?>"
            helper="Използва се при показване и записване на дати и часове в системата."
            full-width
        >
            <?php foreach (
                $timezones
                as $optionValue => $optionLabel
            ): ?>
                <option
                    value="<?= View::escape(
                        $optionValue
                    ) ?>"
                >
                    <?= View::escape(
                        $optionLabel
                    ) ?>
                </option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-dropdown
            name="settings[date_format]"
            label="Формат на датата"
            value="<?= View::escape(
                View::value(
                    $values,
                    'date_format',
                    'd.m.Y'
                )
            ) ?>"
            helper="Определя как ще се визуализират датите в административния панел и сайта."
            full-width
        >
            <?php foreach (
                $dateFormats
                as $optionValue => $optionLabel
            ): ?>
                <option
                    value="<?= View::escape(
                        $optionValue
                    ) ?>"
                >
                    <?= View::escape(
                        $optionLabel
                    ) ?>
                </option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-dropdown
            name="settings[site_default_lang]"
            label="Език на сайта"
            value="<?= View::escape(
                View::value(
                    $values,
                    'site_default_lang',
                    'bg'
                )
            ) ?>"
            helper="Езикът, който ще се използва по подразбиране в публичната част на сайта."
            full-width
        >
            <?php foreach (
                $languages
                as $optionValue => $optionLabel
            ): ?>
                <option
                    value="<?= View::escape(
                        $optionValue
                    ) ?>"
                >
                    <?= View::escape(
                        $optionLabel
                    ) ?>
                </option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-dropdown
            name="settings[admin_default_lang]"
            label="Език на админ панела"
            value="<?= View::escape(
                View::value(
                    $values,
                    'admin_default_lang',
                    'bg'
                )
            ) ?>"
            helper="Езикът, който ще се използва по подразбиране в административния панел."
            full-width
        >
            <?php foreach (
                $languages
                as $optionValue => $optionLabel
            ): ?>
                <option
                    value="<?= View::escape(
                        $optionValue
                    ) ?>"
                >
                    <?= View::escape(
                        $optionLabel
                    ) ?>
                </option>
            <?php endforeach; ?>
        </flex-dropdown>

        <flex-checkbox
            name="settings[debug_mode]"
            value="1"
            label="Debug режим"
            helper="Показва допълнителна информация за грешки. Препоръчително е да бъде изключен в production."
            <?= View::checked(
                $values,
                'debug_mode'
            ) ?>
        ></flex-checkbox>

        <flex-checkbox
            name="settings[enable_multilang]"
            value="1"
            label="Многоезичност"
            helper="Позволява използването на повече от един език в публичната част на сайта."
            <?= View::checked(
                $values,
                'enable_multilang'
            ) ?>
        ></flex-checkbox>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="settings[site_description]"
                label="Кратко описание"
                value="<?= View::escape(
                    View::value(
                        $values,
                        'site_description',
                        ''
                    )
                ) ?>"
                rows="4"
                helper="Кратко описание на сайта, което може да се използва в metadata и други системни места."
                full-width
            ></flex-input>
        </div>

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
