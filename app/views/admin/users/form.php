<?php
use Flex\Core\Auth;

$user = $user ?? null;
$allRoles = $allRoles ?? [];
$assignedRoleIds = $assignedRoleIds ?? [];

$isEdit = isset($user->id);
$currentUserId = Auth::user()?->id;
$isSelf = $isEdit && ($currentUserId === $user?->id);
$action = $isEdit ? '/admin/users/update/' . $user->id : '/admin/users/store';
?>

<flex-form action="<?= $action ?>" method="POST" enctype="multipart/form-data" ajax>

    <flex-tabs variant="line">

        <flex-tab-panel key="general" label="Основни данни" icon="fa-solid fa-user">
            <flex-grid cols="1" md-cols="2" gap="5" class="space-y-5">
                <flex-input name="fullname" label="Пълно име" value="<?= htmlspecialchars($user?->fullname ?? '') ?>"
                    required placeholder="Иван Иванов">
                </flex-input>

                <flex-input type="email" name="email" label="Имейл адрес"
                    value="<?= htmlspecialchars($user?->email ?? '') ?>" placeholder="ivan@example.com" required
                    <?= $isEdit && !empty($user->email) ? 'readonly' : '' ?>>
                </flex-input>
            </flex-grid>

            <flex-grid cols="1" md-cols="2" gap="5" class="space-y-5">
                <flex-input type="password" name="password" label="Нова парола" placeholder="••••••••">
                </flex-input>

                <flex-input type="password" name="password_confirmation" label="Повтори паролата"
                    placeholder="••••••••">
                </flex-input>
            </flex-grid>

            <flex-image-upload name="featured_image" label="Изображение на профила"
                value="<?= htmlspecialchars($user->options['featured_image'] ?? '') ?>" description="400x400px">
            </flex-image-upload>

            <?php if (!$isSelf): ?>
                <flex-grid cols="1" md-cols="2" gap="5">
                    <flex-toggle name="is_active" label="Активен потребител"
                        description="Ако е деактивирано, този потребител няма да може да влиза в профила си."
                        <?= ($user->is_active ?? true) ? 'checked' : '' ?>>
                    </flex-toggle>
                </flex-grid>
            <?php endif; ?>
        </flex-tab-panel>

        <flex-tab-panel key="roles" label="Роли" icon="fa-solid fa-shield-halved"
            badge="<?= count($assignedRoleIds) ?>">
            <?php if ($isSelf): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Не можете да променяте ролите на собствения си профил.
                </p>
            <?php elseif (empty($allRoles)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Няма налични роли в системата.
                </p>
            <?php else: ?>
                <flex-grid cols="1" md-cols="3" gap="5">
                    <?php foreach ($allRoles as $role): ?>
                        <flex-toggle name="roles[<?= $role->id ?>]" label="<?= htmlspecialchars($role->name) ?>"
                            description="<?= htmlspecialchars($role->description ?? '') ?>"
                            class="border border-gray-200 dark:border-gray-800 rounded-md bg-gray-300 dark:bg-gray-950 py-3 px-5"
                            <?= in_array($role->id, $assignedRoleIds) ? 'checked' : '' ?>>
                        </flex-toggle>
                    <?php endforeach; ?>
                </flex-grid>
            <?php endif; ?>
        </flex-tab-panel>

    </flex-tabs>

    <flex-button type="submit" label="<?= $isEdit ? 'Запазване' : 'Създаване' ?>" icon="fa-solid fa-floppy-disk"></flex-button>

</flex-form>