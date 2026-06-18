<?php

use Flex\Core\Routing\View;
use Flex\Core\UI\Form;
?>

<?php Form::renderTabs($definedGroups, $currentGroup, '/admin/settings/'); ?>

<?php Form::create(['action' => "/admin/settings/{$currentGroup}/update", 'method' => 'POST']); ?>
    
    <?php View::component($currentGroup, [
        'group' => $currentGroup,
        'dateFormats' => $dateFormats,
        'languages' => $languages,
        'timezones' => $timezones
    ], 'admin/settings') ?>

    <?php Form::submit('Запазване на промените', 'fa-save'); ?>

<?php Form::close(); ?>