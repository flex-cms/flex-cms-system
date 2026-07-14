<?php

use Flex\Core\Vite;

$sidebarOpen = $_SESSION['sidebar_open'] ?? true;
$darkMode = $_SESSION['dark_mode'] ?? false;

$currentConfig = require base_path('version.php');
$currentVersion = $currentConfig['version'];
?>

<html lang="bg"
    x-data="sidebar('admin-sidebar', <?= $sidebarOpen ? 'true' : 'false' ?>, <?= $darkMode ? 'true' : 'false' ?>)"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

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

    <?= Vite::use('admin')->port(3000) ?>

    <script>
        document.addEventListener('alpine:init', () => {
            document.body.classList.add('alpine-ready');
        });
    </script>
</head>

<body class="bg-gray-100 dark:bg-slate-900 dark:text-white min-h-screen">

    <div class="flex items-center justify-center py-10 md:py-20">
        <div class="max-w-4xl w-full bg-white dark:bg-slate-800 p-5 md:p-10 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-5 text-center">Flex CMS Инсталатор</h1>

            <div class="content">
                <?php echo $content; ?>
            </div>
        </div>
    </div>

</body>

</html>