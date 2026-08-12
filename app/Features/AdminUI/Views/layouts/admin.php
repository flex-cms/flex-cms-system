<?php

declare(strict_types=1);

use Flex\Features\AdminUI\Services\AdminUIAssets;

if (!isset($adminUIAssets) || !$adminUIAssets instanceof AdminUIAssets) {
    throw new RuntimeException('AdminUI layout requires AdminUIAssets.');
}

$title = isset($title) && is_string($title) ? $title : 'Flex CMS';
$content = isset($content) && is_string($content) ? $content : '';
$adminUIConfig = isset($adminUIConfig) && is_array($adminUIConfig) ? $adminUIConfig : [];
$adminUISidebar = isset($adminUISidebar) && is_array($adminUISidebar) ? $adminUISidebar : [];

require __DIR__ . '/partials/context.php';
require __DIR__ . '/partials/navigation.php';
require __DIR__ . '/partials/flash.php';
?>
<!doctype html>
<html lang="bg" data-theme-preference="<?= $escape($themePreference) ?>">
<head>
    <?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="flex-admin-ui min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100" data-turbo="<?= $turboEnabled ? 'true' : 'false' ?>">
    <flex-admin-shell sidebar-position="<?= $escape($sidebarPosition) ?>">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>
        <?php require __DIR__ . '/partials/header.php'; ?>
        <?php require __DIR__ . '/partials/content.php'; ?>
        <?php require __DIR__ . '/partials/footer.php'; ?>
    </flex-admin-shell>
</body>
</html>
