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
            Допълнителни полета за „<?= $escape($page->name) ?>“.
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
                href="/admin/pages/<?= (int) $page->id ?>/fields/import"
                variant="secondary"
                label="Импортирай JSON"
                icon="fa-solid fa-file-import"
                turbo
            ></flex-button>

            <flex-button
                href="/admin/pages/<?= (int) $page->id ?>/fields/create"
                variant="primary"
                label="Ново поле"
                icon="fa-solid fa-plus"
                turbo
            ></flex-button>
        </div>
    </div>

    <flex-data-table
        id="page-fields-table"
        data-page-id="<?= (int) $page->id ?>"
        row-key="id"
        empty-title="Няма добавени полета"
        empty-description="Добавете първото поле или импортирайте готова JSON конфигурация."
        hoverable
        paginated
        searchable
    ></flex-data-table>
</div>
