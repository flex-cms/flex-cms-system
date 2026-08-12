<?php

declare(strict_types=1);

use Flex\Features\Authentication\Models\Role;

/** @var Role|null $role */
$role = $role ?? null;
$permissions = $permissions ?? [];

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$isEdit = $role !== null;
$action = $isEdit
    ? '/admin/authentication/roles/' . (int) $role->id . '/update'
    : '/admin/authentication/roles/store';

$assignedPermissionIds = $role?->permissions
    ?->pluck('id')
    ->map(static fn($id): int => (int) $id)
    ->all() ?? [];
?>

<flex-form
    id="authentication-role-form"
    action="<?= $escape($action) ?>"
    method="POST"
    mode="api"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flex-input
            name="name"
            label="Име"
            value="<?= $escape($role?->name ?? '') ?>"
            placeholder="Напр. Редактор"
            helper="Четимо име, което се показва при задаване на роли."
            maxlength="50"
            icon="fa-solid fa-user-shield"
            required
            full-width
        ></flex-input>

        <flex-input
            name="slug"
            label="Ключ"
            value="<?= $escape($role?->slug ?? '') ?>"
            placeholder="editor"
            helper="Уникален системен ключ с латински букви, цифри, точка, тире или долна черта."
            maxlength="50"
            icon="fa-solid fa-key"
            required
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-input
                type="textarea"
                name="description"
                label="Описание"
                value="<?= $escape($role?->description ?? '') ?>"
                placeholder="Опишете предназначението на ролята..."
                helper="Помага на администраторите да изберат правилната роля."
                rows="4"
                full-width
            ></flex-input>
        </div>

        <flex-input
            type="number"
            name="priority"
            label="Приоритет"
            value="<?= (int) ($role?->priority ?? 0) ?>"
            placeholder="0"
            helper="Използва се за подреждане на ролите."
            full-width
        ></flex-input>

        <flex-input
            name="color"
            label="Цвят"
            value="<?= $escape($role?->color ?? '#6366f1') ?>"
            placeholder="#6366f1"
            helper="HEX цвят във формат #RRGGBB."
            pattern="#[0-9a-fA-F]{6}"
            maxlength="7"
            full-width
        ></flex-input>

        <div class="lg:col-span-2">
            <flex-checkbox
                name="is_active"
                value="1"
                label="Активна роля"
                helper="Разрешенията от неактивна роля не дават достъп."
                <?= ($role?->is_active ?? true) ? 'checked' : '' ?>
            ></flex-checkbox>
        </div>

        <fieldset class="lg:col-span-2 space-y-5">
            <div>
                <legend class="text-lg font-semibold text-slate-900 dark:text-white">
                    Разрешения
                </legend>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Потребителят получава обединението от разрешенията на всички свои активни роли.
                </p>
            </div>

            <?php if (count($permissions) === 0): ?>
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Няма активни разрешения за задаване.
                </p>
            <?php else: ?>
                <?php foreach ($permissions as $module => $items): ?>
                    <section class="space-y-3">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <?php foreach ($items as $permission): ?>
                                <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                                    <flex-checkbox
                                        name="permissions[<?= (int) $permission->id ?>]"
                                        value="1"
                                        label="<?= $escape($permission->name) ?>"
                                        helper="<?= $escape($permission->slug) ?>"
                                        <?= in_array(
                                            (int) $permission->id,
                                            $assignedPermissionIds,
                                            true
                                        ) ? 'checked' : '' ?>
                                    ></flex-checkbox>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>

        <div class="lg:col-span-2 flex items-center gap-3">
            <flex-button
                type="submit"
                variant="primary"
                label="<?= $isEdit ? 'Запази промените' : 'Създай роля' ?>"
                icon="fa-solid fa-floppy-disk"
            ></flex-button>

            <flex-button
                href="/admin/authentication/roles"
                variant="secondary"
                label="Отказ"
                icon="fa-solid fa-xmark"
                turbo
            ></flex-button>
        </div>
    </div>
</flex-form>
