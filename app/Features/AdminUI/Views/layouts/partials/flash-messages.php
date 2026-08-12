<?php

declare(strict_types=1);
?>
<?php foreach ($flashMessages as $flash): ?>
    <?php
    $flashClasses = match ($flash['type']) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200',
        'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-200',
        default => 'border-slate-200 bg-white text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200',
    };
    ?>
    <div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm <?= $flashClasses ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>">
        <i class="fa-solid <?= $escape($flash['icon']) ?> mt-0.5 text-lg" aria-hidden="true"></i>
        <div class="min-w-0 flex-1">
            <strong class="block text-sm font-semibold"><?= $escape($flash['label']) ?></strong>
            <span class="mt-0.5 block text-sm opacity-90"><?= $escape($flash['message']) ?></span>
        </div>
    </div>
<?php endforeach; ?>
