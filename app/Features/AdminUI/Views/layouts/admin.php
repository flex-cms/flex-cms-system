<?php

declare(strict_types=1);

use Flex\Core\Auth;
use Flex\Core\Flex;
use Flex\Features\AdminUI\Services\AdminUIAssets;

if (
    !isset($adminUIAssets)
    || !$adminUIAssets instanceof AdminUIAssets
) {
    throw new RuntimeException(
        'AdminUI layout requires AdminUIAssets.'
    );
}

$title = isset($title) && is_string($title)
    ? $title
    : 'Flex CMS';

$content = isset($content) && is_string($content)
    ? $content
    : '';

$adminUIConfig = isset($adminUIConfig)
    && is_array($adminUIConfig)
        ? $adminUIConfig
        : [];

$currentUser = Auth::user();

$userName = is_string(
    $currentUser?->fullname ?? null
)
    ? $currentUser->fullname
    : 'Гост';

$userEmail = is_string(
    $currentUser?->email ?? null
)
    ? $currentUser->email
    : '';

$userOptions = $currentUser?->options ?? [];

if (!is_array($userOptions)) {
    $userOptions = [];
}

$userTheme = $userOptions['theme'] ?? null;

$themePreference = in_array(
    $userTheme,
    ['light', 'dark', 'system'],
    true
)
    ? $userTheme
    : (
        is_string(
            $adminUIConfig['defaultTheme'] ?? null
        )
            ? $adminUIConfig['defaultTheme']
            : 'system'
    );

$userInitial = function_exists('mb_substr')
    ? mb_substr($userName, 0, 1, 'UTF-8')
    : substr($userName, 0, 1);

$adminName = is_string(
    $adminUIConfig['name'] ?? null
)
    ? $adminUIConfig['name']
    : 'Flex CMS';

$turboEnabled = (
    $adminUIConfig['turboEnabled']
    ?? true
) === true;

$escape = static fn (
    mixed $value
): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$flashTypes = [
    'success' => [
        'label' => 'Успешно',
        'icon' => 'fa-circle-check',
    ],
    'error' => [
        'label' => 'Грешка',
        'icon' => 'fa-circle-xmark',
    ],
    'warning' => [
        'label' => 'Предупреждение',
        'icon' => 'fa-triangle-exclamation',
    ],
];

$flashMessages = [];

foreach ($flashTypes as $type => $definition) {
    $key = 'flash_' . $type;

    if (
        isset($_SESSION[$key])
        && is_string($_SESSION[$key])
    ) {
        $flashMessages[] = [
            'type' => $type,
            'message' => $_SESSION[$key],
            ...$definition,
        ];

        unset($_SESSION[$key]);
    }
}
?>
<!doctype html>
<html
    lang="bg"
    data-theme-preference="<?= $escape(
        $themePreference
    ) ?>"
>
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title><?= $escape($title) ?></title>

    <?= $adminUIAssets->turboMetaTags() ?>

    <?= $adminUIAssets->themeBootstrap(
        $themePreference
    ) ?>

    <?= $adminUIAssets->styles() ?>

    <?= $adminUIAssets->scripts() ?>
</head>

<body
    class="flex-admin-ui"
    data-turbo="<?= $turboEnabled
        ? 'true'
        : 'false' ?>"
>
    <a
        class="flex-skip-link"
        href="#flex-main-content"
    >
        Към основното съдържание
    </a>

    <flex-admin-shell>
        <flex-sidebar
            slot="sidebar"
            brand-name="<?= $escape($adminName) ?>"
            brand-url="/admin"
            version="<?= $escape(Flex::VERSION) ?>"
        >
            <div
                slot="navigation"
                class="flex-sidebar-navigation"
            >
                <flex-nav-item
                    href="/admin"
                    label="Табло"
                    icon="fa-solid fa-gauge-high"
                    exact
                ></flex-nav-item>

                <flex-nav-item
                    href="/admin/settings/general"
                    label="Настройки"
                    icon="fa-solid fa-gear"
                    turbo
                ></flex-nav-item>

                <flex-nav-item
                    href="/admin/users"
                    label="Потребители"
                    icon="fa-solid fa-users"
                ></flex-nav-item>

                <flex-nav-item
                    href="/admin/pages"
                    label="Страници"
                    icon="fa-solid fa-file-lines"
                ></flex-nav-item>

                <flex-nav-item
                    href="/admin/plugins"
                    label="Разширения"
                    icon="fa-solid fa-puzzle-piece"
                ></flex-nav-item>
            </div>
        </flex-sidebar>

        <flex-admin-header
            slot="header"
            title="<?= $escape($title) ?>"
            user-name="<?= $escape($userName) ?>"
            user-email="<?= $escape($userEmail) ?>"
            user-initial="<?= $escape(
                $userInitial ?: 'G'
            ) ?>"
        >
            <?php if (
                isset($primaryButton)
                && is_array($primaryButton)
            ): ?>
                <a
                    slot="actions"
                    class="flex-primary-action"
                    href="<?= $escape(
                        $primaryButton['url'] ?? '#'
                    ) ?>"
                    data-turbo="false"
                >
                    <?php if (
                        !empty($primaryButton['icon'])
                    ): ?>
                        <i
                            class="fa-solid <?= $escape(
                                $primaryButton['icon']
                            ) ?>"
                            aria-hidden="true"
                        ></i>
                    <?php endif; ?>

                    <span>
                        <?= $escape(
                            $primaryButton['label']
                            ?? 'Добави'
                        ) ?>
                    </span>
                </a>
            <?php endif; ?>
        </flex-admin-header>

        <div
            id="flex-main-content"
            slot="content"
            class="flex-page-content"
            tabindex="-1"
        >
            <?php foreach (
                $flashMessages as $flash
            ): ?>
                <div
                    class="flex-server-alert flex-server-alert--<?= $escape(
                        $flash['type']
                    ) ?>"
                    role="<?= $flash['type'] === 'error'
                        ? 'alert'
                        : 'status' ?>"
                >
                    <i
                        class="fa-solid <?= $escape(
                            $flash['icon']
                        ) ?>"
                        aria-hidden="true"
                    ></i>

                    <div>
                        <strong>
                            <?= $escape(
                                $flash['label']
                            ) ?>
                        </strong>

                        <span>
                            <?= $escape(
                                $flash['message']
                            ) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="flex-admin-page">
                <?= $content ?>
            </div>
        </div>

        <div
            slot="footer"
            class="flex-admin-footer"
        >
            <span>
                &copy; <?= date('Y') ?>
                <?= $escape($adminName) ?>
            </span>

            <span>
                версия <?= $escape(Flex::VERSION) ?>
            </span>
        </div>
    </flex-admin-shell>
</body>
</html>