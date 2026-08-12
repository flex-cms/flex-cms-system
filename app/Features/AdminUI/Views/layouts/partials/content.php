<?php

declare(strict_types=1);
?>
<div id="flex-main-content" slot="content" class="min-h-[calc(100vh-var(--flex-header-height))] px-4 py-5 sm:px-6 sm:py-6 xl:px-8" tabindex="-1">
    <div class="mx-auto w-full max-w-400">
        <?php require __DIR__ . '/flash-messages.php'; ?>
        <?= $content ?>
    </div>
</div>
