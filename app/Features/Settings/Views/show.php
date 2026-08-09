<?php

declare(strict_types=1);

use Flex\Core\UI\Form;

$definedGroups = $definedGroups ?? [];
$currentGroup = $currentGroup ?? '';
$settings = $settings ?? [];
$languages = $languages ?? [];
$timezones = $timezones ?? [];
$dateFormats = $dateFormats ?? [];

$groupView = __DIR__
    . DIRECTORY_SEPARATOR
    . 'groups'
    . DIRECTORY_SEPARATOR
    . $currentGroup
    . '.php';

if (!is_file($groupView)) {
    throw new RuntimeException(
        sprintf(
            'Settings view for group [%s] was not found.',
            $currentGroup
        )
    );
}
?>

<?php Form::renderTabs(
    $definedGroups,
    $currentGroup,
    '/admin/settings/'
); ?>

<?php Form::create([
    'action' => '/admin/settings/'
        . rawurlencode($currentGroup)
        . '/update',
    'method' => 'POST',
]); ?>

    <?php require $groupView; ?>

    <?php Form::submit(
        'Запазване на промените',
        'fa-save'
    ); ?>

<?php Form::close(); ?>
