<?php

use Flex\Core\UI\Form;
use Flex\Models\Setting;
?>

<?php Form::section(title: 'Поведение на файловете', slot: function () { ?>
    <?php Form::row(function () {
        
        Form::toggle('settings[media_use_date_folders]', 'Подреждане в папки по дата', [
            'value' => Setting::getValue('media_use_date_folders', true),
            'description' => 'Автоматично създава подпапки (година/месец/ден) при качване на файлове.'
        ]);

        Form::toggle('settings[media_keep_original_name]', 'Запазване на оригиналното име', [
            'value' => Setting::getValue('media_keep_original_name', false),
            'description' => 'Ако е изключено, имената се променят на уникални идентификатори.'
        ]);

    }, ['md' => 2]); ?>
<?php }); ?>

<?php Form::section(title: 'Ограничения и сигурност', slot: function () { ?>
    <?php Form::row(function () {

        Form::input('settings[media_max_size]', 'Максимален размер (MB)', [
            'value' => Setting::getValue('media_max_size', 5),
            'description' => 'Максимален размер на файл за качване в мегабайти.'
        ]);

        Form::input('settings[media_allowed_extensions]', 'Разрешени разширения', [
            'value' => Setting::getValue('media_allowed_extensions', 'jpg,png,webp'),
            'description' => 'Изброени със запетая (напр. jpg,png,pdf).'
        ]);

    }, ['md' => 2]); ?>
<?php }); ?>