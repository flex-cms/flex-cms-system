<?php

declare(strict_types=1);
use Flex\Core\Flex;
?>
<div slot="footer" class="flex flex-col gap-1 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:text-slate-400">
    <span>&copy; <?= date('Y') ?> <?= $escape($adminName) ?></span>
    <span>версия <?= $escape(Flex::VERSION) ?></span>
</div>
