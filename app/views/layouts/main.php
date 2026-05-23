<?php

use Flex\Core\Events\EventManager;
use Flex\Core\Vite;
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $title ?? 'Flex CMS'; ?>
    </title>

    <?php EventManager::getInstance()->trigger('view.head'); ?>

    <?= Vite::use('main') ?>
</head>

<body
    class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-300">

    <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm">
        <nav class="container mx-auto px-4 h-16 flex items-center justify-between">
            <div class="text-xl font-bold bg-linear-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">
                Flex CMS
            </div>

            <div class="flex items-center gap-6 font-medium">
                <a href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Начало</a>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <a href="/about" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">За нас</a>
            </div>

            <button onclick="document.documentElement.classList.toggle('dark')"
                class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700">
                🌓
            </button>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-12 grow">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <?php echo $content; ?>
        </div>
    </main>

    <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-auto">
        <div class="container mx-auto px-4 py-6 text-center text-slate-500 dark:text-slate-400 text-sm">
            <p>&copy; <?php echo date('Y'); ?>
                <span class="font-semibold">Flex CMS Plugin System</span>.
                Всички права запазени.
            </p>
        </div>
    </footer>

    <?php EventManager::getInstance()->trigger('view.footer'); ?>
</body>

</html>
