<?php

declare(strict_types=1);

use Flex\Core\Auth;
use Flex\Core\Flex;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
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

$adminUISidebar = isset($adminUISidebar)
    && is_array($adminUISidebar)
    ? $adminUISidebar
    : [];

$sidebarItems = isset(
    $adminUISidebar['items']
)
    && is_array($adminUISidebar['items'])
    ? $adminUISidebar['items']
    : [];

$sidebarPosition = in_array(
    $adminUISidebar['position'] ?? null,
    ['left', 'right'],
    true
)
    ? $adminUISidebar['position']
    : 'left';

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
    ? mb_substr(
        $userName,
        0,
        1,
        'UTF-8'
    )
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

$escape = static fn(
    mixed $value
): string => htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

/**
 * Рекурсивно визуализира navigation items.
 *
 * @param array<int, mixed> $items
 */
$renderNavigationItems = null;

$renderNavigationItems = static function (array $items) use (
    &$renderNavigationItems,
    $escape,
    $turboEnabled
): void {
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = $item['id'] ?? '';
        $label = $item['label'] ?? '';
        $url = $item['url'] ?? '#';

        $icon = $item['icon']
            ?? 'fa-solid fa-circle';

        $badge = $item['badge'] ?? '';

        $children = $item['children']
            ?? [];

        if (!is_array($children)) {
            $children = [];
        }

        if ($children !== []) {
            ?>
            <flex-nav-group
                data-navigation-id="<?= $escape(
                    $id
                ) ?>"
                label="<?= $escape(
                    $label
                ) ?>"
                icon="<?= $escape(
                    $icon
                ) ?>"
                <?php if ($badge !== ''): ?>
                    badge="<?= $escape(
                        $badge
                    ) ?>"
                <?php endif; ?>
            >
                <?php $renderNavigationItems(
                    $children
                ); ?>
            </flex-nav-group>
            <?php

            continue;
        }
        ?>
        <flex-nav-item
            data-navigation-id="<?= $escape(
                $id
            ) ?>"
            href="<?= $escape(
                $url
            ) ?>"
            label="<?= $escape(
                $label
            ) ?>"
            icon="<?= $escape(
                $icon
            ) ?>"
            <?php if ($badge !== ''): ?>
                badge="<?= $escape(
                    $badge
                ) ?>"
            <?php endif; ?>
            <?php if (
                !empty($item['target'])
            ): ?>
                target="<?= $escape(
                    $item['target']
                ) ?>"
            <?php endif; ?>
            <?php if (
                $turboEnabled
                && ($item['turbo'] ?? false)
                    === true
            ): ?>
                turbo
            <?php endif; ?>
            <?php if (
                ($item['exact'] ?? false)
                    === true
            ): ?>
                exact
            <?php endif; ?>
        ></flex-nav-item>
        <?php
    }
};

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

foreach (
    $flashTypes as $type => $definition
) {
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
<html lang="bg" data-theme-preference="<?= $escape(
    $themePreference
) ?>">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $escape($title) ?></title>

    <?= $adminUIAssets->turboMetaTags() ?>

    <?= $adminUIAssets->themeBootstrap(
        $themePreference
    ) ?>

    <?= $adminUIAssets->styles() ?>

    <?= $adminUIAssets->scripts() ?>
</head>

<body class="flex-admin-ui min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100"
    data-turbo="<?php if (
        $turboEnabled
        && ($item['turbo'] ?? false) === true
    ): ?>
        turbo
    <?php endif; ?>">

    <flex-admin-shell sidebar-position="<?= $escape(
        $sidebarPosition
    ) ?>">
        <flex-sidebar slot="sidebar" brand-name="<?= $escape(
            $adminName
        ) ?>" brand-url="/admin" version="<?= $escape(
             Flex::VERSION
         ) ?>">
            <div slot="navigation" class="flex flex-col gap-1" data-sidebar-id="<?= $escape(
                $adminUISidebar['id']
                ?? SidebarRegistry::DEFAULT_SIDEBAR
            ) ?>">
                <?php if (
                    $sidebarItems !== []
                ): ?>
                    <?php $renderNavigationItems(
                        $sidebarItems
                    ); ?>
                <?php else: ?>
                    <div
                        class="rounded-xl border border-dashed border-slate-300 px-3 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Няма регистрирани линкове.
                    </div>
                <?php endif; ?>
            </div>
        </flex-sidebar>

        <flex-admin-header slot="header" title="<?= $escape($title) ?>" user-name="<?= $escape(
              $userName
          ) ?>" user-email="<?= $escape(
               $userEmail
           ) ?>" user-initial="<?= $escape(
                $userInitial ?: 'G'
            ) ?>">
            <?php if (
                isset($primaryButton)
                && is_array($primaryButton)
            ): ?>
                <a slot="actions"
                    class="flex-primary-action inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    href="<?= $escape(
                        $primaryButton['url']
                        ?? '#'
                    ) ?>" data-turbo="<?= (
                            $turboEnabled
                            && ($primaryButton['turbo'] ?? false)
                        ) ? 'true' : 'false' ?>">
                    <?php if (
                        !empty(
                        $primaryButton['icon']
                    )
                    ): ?>
                        <i class="fa-solid <?= $escape(
                            $primaryButton['icon']
                        ) ?>" aria-hidden="true"></i>
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

        <div id="flex-main-content" slot="content"
            class="min-h-[calc(100vh-var(--flex-header-height))] px-4 py-5 sm:px-6 sm:py-6 xl:px-8" tabindex="-1">
            <div class="mx-auto w-full max-w-400">
                <?php foreach (
                    $flashMessages as $flash
                ): ?>
                    <?php
                    $flashClasses = match (
                    $flash['type']
                    ) {
                        'success' =>
                        'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200',

                        'error' =>
                        'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200',

                        'warning' =>
                        'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-200',

                        default =>
                        'border-slate-200 bg-white text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200',
                    };
                    ?>

                    <div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm <?= $flashClasses ?>"
                        role="<?= $flash['type']
                            === 'error'
                            ? 'alert'
                            : 'status' ?>">
                        <i class="fa-solid <?= $escape(
                            $flash['icon']
                        ) ?> mt-0.5 text-lg" aria-hidden="true"></i>

                        <div class="min-w-0 flex-1">
                            <strong class="block text-sm font-semibold">
                                <?= $escape(
                                    $flash['label']
                                ) ?>
                            </strong>

                            <span class="mt-0.5 block text-sm opacity-90">
                                <?= $escape(
                                    $flash['message']
                                ) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?= $content ?>
            </div>
        </div>

        <div slot="footer"
            class="flex flex-col gap-1 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:text-slate-400">
            <span>
                &copy; <?= date('Y') ?>
                <?= $escape($adminName) ?>
            </span>

            <span>
                версия <?= $escape(
                    Flex::VERSION
                ) ?>
            </span>
        </div>
    </flex-admin-shell>
</body>

</html>
