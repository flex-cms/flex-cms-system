<?php

declare(strict_types=1);

/** @param array<int, mixed> $items */
$renderNavigationItems = null;

$renderNavigationItems = static function (array $items) use (&$renderNavigationItems, $escape, $turboEnabled): void {
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = $item['id'] ?? '';
        $label = $item['label'] ?? '';
        $url = $item['url'] ?? '#';
        $icon = $item['icon'] ?? 'fa-solid fa-circle';
        $badge = $item['badge'] ?? '';
        $children = $item['children'] ?? [];

        if (!is_array($children)) {
            $children = [];
        }

        if ($children !== []) {
            ?>
            <flex-nav-group data-navigation-id="<?= $escape($id) ?>" label="<?= $escape($label) ?>" icon="<?= $escape($icon) ?>" <?php if ($badge !== ''): ?>badge="<?= $escape($badge) ?>"<?php endif; ?>>
                <?php $renderNavigationItems($children); ?>
            </flex-nav-group>
            <?php
            continue;
        }
        ?>
        <flex-nav-item data-navigation-id="<?= $escape($id) ?>" href="<?= $escape($url) ?>" label="<?= $escape($label) ?>" icon="<?= $escape($icon) ?>" <?php if ($badge !== ''): ?>badge="<?= $escape($badge) ?>"<?php endif; ?> <?php if (!empty($item['target'])): ?>target="<?= $escape($item['target']) ?>"<?php endif; ?> <?php if ($turboEnabled && ($item['turbo'] ?? false) === true): ?>turbo<?php endif; ?> <?php if (($item['exact'] ?? false) === true): ?>exact<?php endif; ?>></flex-nav-item>
        <?php
    }
};
