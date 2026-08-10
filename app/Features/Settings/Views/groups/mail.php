<?php

declare(strict_types=1);

use Flex\Features\Settings\Support\SettingsView as View;

$values = $values ?? [];
?>

<flex-form
    id="settings-mail-form"
    action="/admin/settings/mail/update"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="settings[smtp_host]"
            label="SMTP хост"
            value="<?= View::escape(View::value($values, 'smtp_host', '')) ?>"
            placeholder="smtp.example.com"
            helper="Адресът на SMTP сървъра, използван за изпращане на имейли."
            icon="fa-solid fa-server"
            full-width
        ></flex-input>

        <flex-input
            type="number"
            name="settings[smtp_port]"
            label="SMTP порт"
            value="<?= View::escape(View::value($values, 'smtp_port', 587)) ?>"
            min="1"
            max="65535"
            helper="Портът на SMTP сървъра. Често използвани стойности са 587, 465 и 25."
            full-width
        ></flex-input>

        <flex-input
            name="settings[smtp_user]"
            label="SMTP потребител"
            value="<?= View::escape(View::value($values, 'smtp_user', '')) ?>"
            placeholder="user@example.com"
            helper="Потребителското име, използвано за удостоверяване към SMTP сървъра."
            icon="fa-solid fa-user"
            full-width
        ></flex-input>

        <flex-input
            type="password"
            name="settings[smtp_pass]"
            label="SMTP парола"
            value=""
            placeholder="Въведете нова парола"
            helper="Оставете празно, ако не искате да променяте вече записаната SMTP парола."
            icon="fa-solid fa-lock"
            autocomplete="new-password"
            full-width
        ></flex-input>

        <flex-dropdown
            name="settings[smtp_encryption]"
            label="Криптиране"
            value="<?= View::escape(View::value($values, 'smtp_encryption', 'tls')) ?>"
            helper="Методът за криптиране на връзката със SMTP сървъра."
            full-width
        >
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
            <option value="none">Без криптиране</option>
        </flex-dropdown>

        <flex-input
            type="email"
            name="settings[from_email]"
            label="Имейл на подателя"
            value="<?= View::escape(View::value($values, 'from_email', '')) ?>"
            placeholder="noreply@example.com"
            helper="Имейл адресът, който ще се показва като подател на системните съобщения."
            icon="fa-solid fa-at"
            full-width
        ></flex-input>

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
