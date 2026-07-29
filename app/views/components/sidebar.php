<?php

use Flex\Core\UI\Sidebar;

$currentSidebarName = $sidebarName ?? 'admin_main';

$coreSidebarLinks = Sidebar::getCoreLinks(
    $currentSidebarName
);

$pluginSidebarLinks = Sidebar::getPluginLinks(
    $currentSidebarName
);

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

function renderSidebarLinks(
    array $links,
    string $currentPath,
    bool $pluginLinks = false
): void {
    foreach ($links as $link) {
        $url = (string) ($link['url'] ?? '#');
        $label = (string) ($link['label'] ?? '');
        $icon = (string) (
            $link['icon']
            ?? 'fa-puzzle-piece'
        );

        $children = isset($link['children'])
            && is_array($link['children'])
            ? $link['children']
            : [];

        $hasChildren = $children !== [];

        $isGroupActive = $url !== '#'
            && str_starts_with($currentPath, $url);

        if ($hasChildren) {
            foreach ($children as $child) {
                $childUrl = (string) (
                    $child['url'] ?? ''
                );

                if (
                    $childUrl !== ''
                    && str_starts_with(
                        $currentPath,
                        $childUrl
                    )
                ) {
                    $isGroupActive = true;
                    break;
                }
            }
        }

        $safeUrl = htmlspecialchars(
            $url,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeLabel = htmlspecialchars(
            $label,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeIcon = htmlspecialchars(
            $icon,
            ENT_QUOTES,
            'UTF-8'
        );

        $pluginSlug = htmlspecialchars(
            (string) ($link['plugin'] ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        if ($hasChildren) {
            ?>
            <div x-cloak x-data="{
                    subOpen: <?= $isGroupActive
                        ? 'true'
                        : 'false' ?>
                }" class="space-y-1">
                <button type="button" @click="subOpen = !subOpen"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md font-semibold text-left transition-colors"
                    :class="subOpen
                        ? 'text-white bg-neutral-900/50'
                        : 'text-slate-400 hover:bg-primary hover:text-white'">
                    <div class="flex min-w-0 items-center gap-3">
                        <i class="fa-solid <?= $safeIcon ?> text-xl w-6 shrink-0" :class="subOpen ? 'text-primary' : ''"></i>

                        <span class="truncate">
                            <?= $safeLabel ?>
                        </span>
                    </div>

                    <i class="fa-solid fa-chevron-down text-sm transition-transform duration-200" :class="{
                            'rotate-180': subOpen
                        }"></i>
                </button>

                <div x-show="subOpen" x-collapse style="<?= $isGroupActive
                    ? ''
                    : 'display: none;' ?>" class="sidebar-child-menu">
                    <div class="pl-9 space-y-1">
                        <?php foreach ($children as $child): ?>
                            <?php
                            $childUrl = (string) (
                                $child['url'] ?? '#'
                            );

                            $childLabel = htmlspecialchars(
                                (string) (
                                    $child['label'] ?? ''
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            $safeChildUrl = htmlspecialchars(
                                $childUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            $isChildActive =
                                $childUrl !== '#'
                                && str_starts_with(
                                    $currentPath,
                                    $childUrl
                                );
                            ?>

                            <a href="<?= $safeChildUrl ?>" @click.prevent="navigateTo('<?= $safeChildUrl ?>')" class="block px-3 py-1.5 rounded-md text-base transition-colors <?= $isChildActive
                                    ? 'text-secondary font-bold bg-neutral-900/30'
                                    : 'text-slate-400 hover:text-white'
                                    ?>">
                                <?= $childLabel ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <a href="<?= $safeUrl ?>" class="flex items-center gap-3 px-3 py-2 rounded-md font-semibold transition-colors <?= $isGroupActive
                  ? 'bg-primary text-white'
                  : 'text-slate-400 hover:bg-primary hover:text-white'
                  ?>">
                <i class="fa-solid <?= $safeIcon ?> text-xl w-6 shrink-0"></i>

                <span class="min-w-0 flex-1 truncate">
                    <?= $safeLabel ?>
                </span>

                <?php if ($pluginLinks): ?>
                    <span title="Добавено от плъгин: <?= $pluginSlug ?>"
                        class="shrink-0 rounded bg-primary/15 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">
                        Плъгин
                    </span>
                <?php endif; ?>
            </a>
            <?php
        }
    }
}
?>

<div>
    <div id="sidebar-backdrop" x-show="isOpen" x-cloak @click="toggle()"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden">
    </div>

    <aside id="main-sidebar" :class="{ 
            'translate-x-0': isOpen, 
            '-translate-x-full': !isOpen,
            'transition-transform duration-300': mounted
        }"
        class="min-h-screen fixed inset-y-0 left-0 flex w-72 flex-col bg-black text-white z-50 transform shadow-2xl <?= !$is_open
            ? '-translate-x-full'
            : ''
        ?>">

        <div class="p-5 flex items-center justify-between">
            <div class="flex flex-col justify-center items-center mx-auto text-center">
                <span class="text-xl font-black uppercase text-secondary">Администрация</span>
                <span class="text-sm text-gray-500">Flex CMS</span>
            </div>
            <button @click="toggle()"
                class="lg:hidden flex justify-center items-center rounded-md w-10 h-10 bg-gray-800 hover:bg-gray-900 text-slate-400">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <hr class="border-t border-gray-800" />

        <nav
            x-cloak
            x-data
            class="flex min-h-0 flex-1 flex-col overflow-y-auto text-lg"
        >
            <?php $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>

            <div class="p-2 space-y-1">
                <div class="p-2 space-y-1">
                    <?php renderSidebarLinks(
                        $coreSidebarLinks,
                        $currentPath
                    ); ?>
                </div>

                <?php if ($pluginSidebarLinks !== []): ?>
                    <div class="mt-auto">
                        <div class="px-5 pb-2">
                            <div class="flex items-center gap-2">
                                <div class="h-px flex-1 bg-gray-800"></div>

                                <span
                                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                    <i class="fa-solid fa-plug text-[10px]"></i>
                                    Плъгини
                                </span>

                                <div class="h-px flex-1 bg-gray-800"></div>
                            </div>

                            <p class="mt-1 text-center text-xs text-gray-400">
                                Менюта, добавени от разширения
                            </p>
                        </div>

                        <div class="p-2 pt-0 space-y-1">
                            <?php renderSidebarLinks(
                                $pluginSidebarLinks,
                                $currentPath,
                                true
                            ); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <hr class="border-t border-gray-800" />

            <div class="p-2">
                <form action="/logout" method="GET">
                    <button type="submit"
                        class="w-full flex items-center gap-3 font-semibold px-3 py-2 text-red-500 hover:bg-red-500 hover:text-white rounded-md transition-all group">
                        <i class="fa-solid fa-power-off w-6 group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Изход</span>
                    </button>
                </form>
            </div>
    </aside>
</div>
