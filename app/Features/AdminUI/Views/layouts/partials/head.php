<?php

declare(strict_types=1);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $escape($title) ?></title>
<?= $adminUIAssets->turboMetaTags() ?>
<?= $adminUIAssets->themeBootstrap($themePreference) ?>
<?= $adminUIAssets->styles() ?>
<?= $adminUIAssets->scripts() ?>
