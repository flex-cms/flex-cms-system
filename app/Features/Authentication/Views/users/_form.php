<?php

declare(strict_types=1);

use Flex\Features\Authentication\Models\User;

/** @var User|null $user */
$user = $user ?? null;
$roles = $roles ?? [];

$escape = static fn(mixed $value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$isEdit = $user !== null;
$action = $isEdit
    ? '/admin/authentication/users/' . (int) $user->id . '/update'
    : '/admin/authentication/users/store';

$assignedRoleIds = $user?->roles
    ?->pluck('id')
    ->map(
        static fn($id): int => (int) $id
    )
    ->all() ?? [];
?>

<flex-form
    id="authentication-user-form"
    action="<?= $escape($action) ?>"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="fullname"
            label="Име"
            value="<?= $escape($user?->fullname ?? '') ?>"
            placeholder="Напр. Иван Петров"
            helper="Името, което ще се показва в административния панел."
            maxlength="100"
            icon="fa-solid fa-user"
            required
            full-width
        ></flex-input>

        <flex-input
            type="email"
            name="email"
            label="Имейл"
            value="<?= $escape($user?->email ?? '') ?>"
            placeholder="ivan@example.com"
            helper="Използва се за вход и трябва да бъде уникален."
            maxlength="100"
            autocomplete="email"
            icon="fa-solid fa-envelope"
            required
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="password"
                name="password"
                label="Парола"
                value=""
                placeholder="Минимум 12 символа"
                helper="<?= $isEdit
                    ? 'Оставете полето празно, ако не искате да променяте паролата.'
                    : 'Задайте парола с минимум 12 символа.' ?>"
                minlength="12"
                autocomplete="new-password"
                icon="fa-solid fa-lock"
                <?= $isEdit ? '' : 'required' ?>
                full-width
            ></flex-input>
        </div>

        <flex-checkbox
            name="is_active"
            value="1"
            label="Активен потребител"
            helper="Неактивният потребител не може да влиза в системата."
            <?= ($user?->is_active ?? true) ? 'checked' : '' ?>
        ></flex-checkbox>

        <flex-checkbox
            name="is_super_admin"
            value="1"
            label="Супер администратор"
            helper="Има пълен достъп и не използва зададените роли. В системата може да има само един."
            <?= ($user?->is_super_admin ?? false) ? 'checked' : '' ?>
        ></flex-checkbox>

        <fieldset class="lg:col-span-2 space-y-3">
            <div>
                <legend class="text-sm font-semibold text-slate-900 dark:text-white">
                    Роли
                </legend>

                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Ролите не се прилагат, когато профилът е супер администратор.
                </p>
            </div>

            <?php if (count($roles) === 0): ?>
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Няма активни роли за задаване.
                </p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <?php foreach ($roles as $role): ?>
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <flex-checkbox
                                name="roles[<?= (int) $role->id ?>]"
                                value="1"
                                label="<?= $escape($role->name) ?>"
                                helper="<?= $escape(
                                    $role->description
                                    ?: 'Без допълнително описание.'
                                ) ?>"
                                <?= in_array(
                                    (int) $role->id,
                                    $assignedRoleIds,
                                    true
                                ) ? 'checked' : '' ?>
                            ></flex-checkbox>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </fieldset>

        <div class="lg:col-span-2 flex items-center gap-3">
            <flex-button
                type="submit"
                variant="primary"
                label="<?= $isEdit
                    ? 'Запази промените'
                    : 'Създай потребител' ?>"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>

            <flex-button
                href="/admin/authentication/users"
                variant="secondary"
                label="Отказ"
                icon="fa-solid fa-xmark"
                turbo
            ></flex-button>
        </div>
    </div>
</flex-form>
