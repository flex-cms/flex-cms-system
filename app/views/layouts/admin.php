<?php

use Flex\Core\Auth;
use Flex\Core\Vite;
use Flex\Core\Routing\View;

$currentUser = Auth::user();
$sidebarOpen = $currentUser->options['sidebar_open'] ?? $_SESSION['sidebar_open'] ?? true;
$darkMode = ($currentUser->options['theme'] ?? null) === 'dark' ?? $_SESSION['dark_mode'] ?? false;
$currentConfig = require dirname(__DIR__, 2) . '/Core/version.php';
$currentVersion = $currentConfig['version'];
?>

<html lang="bg" 
      x-data="sidebar('admin-sidebar', <?= $sidebarOpen ? 'true' : 'false' ?>, <?= $darkMode ? 'true' : 'false' ?>)" 
      :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?></title>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html.dark {
            background-color: #0f172a;
            color: #f1f5f9;
        }

        body {
            margin: 0;
            transition: background-color 0.3s ease;
        }
    </style>

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

    <?= Vite::use('admin') ?>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 min-h-screen font-sans">

    <div class="flex min-h-screen overflow-hidden">

        <?php View::component('sidebar', ['is_open' => $sidebarOpen]); ?>

        <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 <?= $sidebarOpen ? 'lg:pl-72' : 'lg:pl-0' ?>"
            x-data="{ mounted: false }" x-init="$nextTick(() => mounted = true)" :class="{ 
                'lg:pl-72': isOpen, 
                'lg:pl-0': !isOpen,
                'duration-300': mounted 
            }">

            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="toggle()" class="p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>

                    <h1 class="text-lg font-semibold truncate">
                        <?= $title ?? 'Табло'; ?>
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (isset($primaryButton)): ?>
                        <a href="<?= $primaryButton['url'] ?>"
                            class="hidden md:inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all mr-4">
                            <?php if (isset($primaryButton['icon'])): ?>
                                <i class="fa-solid <?= $primaryButton['icon'] ?> mr-2"></i>
                            <?php endif; ?>
                            <?= $primaryButton['label'] ?>
                        </a>
                    <?php endif; ?>

                    <button @click="toggleTheme()" ...>
                        <i class="fa-solid" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                    </button>

                    <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 mx-2"></div>

                    <div class="flex items-center gap-3 pl-2">
                        <?php if ($currentUser): ?>
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium leading-none text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($currentUser->username) ?>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    <?= htmlspecialchars($currentUser->email) ?>
                                </p>
                            </div>
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold shadow-sm">
                                <?= strtoupper(substr($currentUser->username, 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium leading-none text-slate-900 dark:text-white">Гост</p>
                            </div>
                            <div
                                class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                                G
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-2 md:p-4 lg:p-5">
                <div class="animate-fade-in">
                    <?= $content; ?>
                </div>
            </main>

            <footer class="py-4 px-6 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 text-sm text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
                <p>&copy;
                    <?= date('Y'); ?> Flex CMS версия <?= $currentVersion ?>. Всички права запазени.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-indigo-600">Документация</a>
                    <a href="#" class="hover:text-indigo-600">Поддръжка</a>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
