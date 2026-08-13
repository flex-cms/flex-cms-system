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
    <p class="text-sm text-slate-600 dark:text-slate-300">
        Импортирайте готова конфигурация от полета за „<?= $escape($page->name) ?>“.
    </p>

    <flex-form
        id="page-fields-import-form"
        action="/admin/pages/<?= (int) $page->id ?>/fields/import"
        method="POST"
        mode="api"
    >
        <div class="space-y-4">
            <flex-input
                type="textarea"
                name="fields_json"
                label="JSON конфигурация"
                value=""
                placeholder='[{&quot;type&quot;:&quot;text&quot;,&quot;label&quot;:&quot;Заглавие&quot;,&quot;key&quot;:&quot;title&quot;,&quot;group&quot;:&quot;general&quot;,&quot;order&quot;:0,&quot;hint&quot;:null}]'
                helper="Поставете JSON масив от полета. Импортирането ще валидира целия файл преди запис."
                rows="14"
                icon="fa-solid fa-code"
                required
                full-width
            ></flex-input>

            <?php /* TODO: Добавяне на flex-file-upload компонент за директно избиране на .json файл. */ ?>

            <div class="flex flex-wrap items-center gap-3">
                <flex-button
                    type="submit"
                    variant="primary"
                    label="Импортирай полетата"
                    icon="fa-solid fa-file-import"
                ></flex-button>

                <flex-button
                    href="/admin/pages/<?= (int) $page->id ?>/fields"
                    variant="secondary"
                    label="Отказ"
                    icon="fa-solid fa-xmark"
                    turbo
                ></flex-button>
            </div>
        </div>
    </flex-form>
</div>
