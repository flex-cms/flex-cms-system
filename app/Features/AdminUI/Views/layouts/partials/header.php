<?php

declare(strict_types=1);
?>
<flex-admin-header slot="header" title="<?= $escape($title) ?>" user-name="<?= $escape($userName) ?>" user-email="<?= $escape($userEmail) ?>" user-initial="<?= $escape($userInitial ?: 'G') ?>">
    <?php if (isset($primaryButton) && is_array($primaryButton)): ?>
        <a slot="actions" class="flex-primary-action inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 dark:bg-indigo-500 dark:hover:bg-indigo-400" href="<?= $escape($primaryButton['url'] ?? '#') ?>" data-turbo="<?= ($turboEnabled && ($primaryButton['turbo'] ?? false)) ? 'true' : 'false' ?>">
            <?php if (!empty($primaryButton['icon'])): ?>
                <i class="fa-solid <?= $escape($primaryButton['icon']) ?>" aria-hidden="true"></i>
            <?php endif; ?>
            <span><?= $escape($primaryButton['label'] ?? 'Добави') ?></span>
        </a>
    <?php endif; ?>
</flex-admin-header>
