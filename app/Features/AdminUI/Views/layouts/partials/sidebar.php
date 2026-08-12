<?php

declare(strict_types=1);
use Flex\Core\Flex;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
?>
<flex-sidebar slot="sidebar" brand-name="<?= $escape($adminName) ?>" brand-url="/admin" version="<?= $escape(Flex::VERSION) ?>">
    <div slot="navigation" class="flex flex-col gap-1" data-sidebar-id="<?= $escape($adminUISidebar['id'] ?? SidebarRegistry::DEFAULT_SIDEBAR) ?>">
        <?php if ($sidebarItems !== []): ?>
            <?php $renderNavigationItems($sidebarItems); ?>
        <?php else: ?>
            <div class="rounded-xl border border-dashed border-slate-300 px-3 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">Няма регистрирани линкове.</div>
        <?php endif; ?>
    </div>
</flex-sidebar>
