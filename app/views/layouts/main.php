<?php

use Flex\Core\Auth;
use Flex\Core\Helpers\Flash;
use Flex\Core\Vite;
use Flex\Core\Routing\View;

$currentUser = Auth::user();
$sidebarOpen = $currentUser->options['sidebar_open'] ?? $_SESSION['sidebar_open'] ?? true;
$darkMode = ($currentUser->options['theme'] ?? null) === 'dark' ?? $_SESSION['dark_mode'] ?? false;
$currentConfig = require base_path('version.php');
$currentVersion = $currentConfig['version'];
?>

<html lang="bg"
    x-data="sidebar('admin-sidebar', <?= $sidebarOpen ? 'true' : 'false' ?>, <?= $darkMode ? 'true' : 'false' ?>)"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? '' ?></title>

    <script>
        (function () {
            const isDark = <?= $darkMode ? 'true' : 'false' ?>;
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <style>
        body {
            opacity: 0;
        }

        body.alpine-ready {
            opacity: 1;
            transition: opacity 0s ease-out;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <?= Vite::use('admin') ?>

    <script>
        document.addEventListener('alpine:init', () => {
            document.body.classList.add('alpine-ready');
        });
    </script>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 min-h-screen font-sans">
    <main class="flex-1 overflow-y-auto p-2 md:p-4 lg:p-5">
        <?= Flash::render(); ?>
        <div class="animate-fade-in">
            <?= $content; ?>
        </div>
    </main>
</body>

</html>
