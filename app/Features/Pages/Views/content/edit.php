<?php

declare(strict_types=1);

use Flex\Features\Pages\Models\Page;

/** @var Page $page */
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Управление на структурата и съдържанието на „<?= $escape($page->name) ?>“.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <flex-button
                href="/admin/pages/edit/<?= (int) $page->id ?>"
                variant="secondary"
                label="Настройки на страницата"
                icon="fa-solid fa-gear"
                turbo
            ></flex-button>

            <flex-button
                id="page-content-save"
                type="button"
                variant="primary"
                label="Запази съдържанието"
                icon="fa-solid fa-floppy-disk"
                disabled
            ></flex-button>
        </div>
    </div>

    <flex-page-builder
        id="page-content-builder"
        data-page-id="<?= (int) $page->id ?>"
    ></flex-page-builder>
</div>
