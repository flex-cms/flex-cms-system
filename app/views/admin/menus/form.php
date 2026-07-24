<?php

use Flex\Core\Routing\View;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$isEdit = isset($menu->id);
$action = $isEdit ? "/admin/menus/update/{$menu->id}" : "/admin/menus/store";

Page::header(
    title: $menu ? 'Редактиране на меню' : 'Създаване на меню',
    backUrl: '/admin/menus',
    subtitle: $menu ? 'Променете структурата и настройките на менюто' : 'Създайте ново меню за вашия сайт'
);
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'files' => true]) ?>

<?php Form::section(title: 'Основни данни', slot: function () use ($menu) { ?>

    <?php Form::row(function () use ($menu) {

        Form::input(
            'name',
            'Име на менюто',
            [
                'value' => $menu->name ?? '',
                'required' => true,
                'hint' => 'Използва се за вътрешно разпознаване (напр. "Главно меню", "Footer Menu")'
            ]
        );

        Form::input(
            'slug',
            'Уникален код (ID)',
            [
                'value' => $menu->slug ?? '',
                'placeholder' => 'main-menu',
                'hint' => 'Можете да зададете собствен ключ или да го оставите празно.',
            ]
        );

    }, ['md' => 2]);

}); ?>

<?php Form::row(function () use ($menu, $menuItems) { ?>

    <?php Form::section(title: 'Елементи на менюто', slot: function () use ($menu, $menuItems) { ?>

        <div class="mb-4">
            <span class="block font-semibold text-slate-700 dark:text-slate-300 mb-2">Структура на линковете</span>

            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Добавете елементи към менюто, техните линкове и подредба.
            </p>
        </div>

        <?php
        $baseFields = [
            'label' => [
                'label' => 'Текст на линка',
                'type' => 'text'
            ],
            'url' => [
                'label' => 'URL адрес',
                'type' => 'text'
            ],
            'target' => [
                'label' => 'Отваряне',
                'type' => 'select',
                'options' => [
                    '_self' => 'В същия прозорец',
                    '_blank' => 'В нов прозорец (_blank)'
                ]
            ],
            'is_active' => [
                'label' => 'Активен линк',
                'type' => 'toggle'
            ],
            'description' => [
                'label' => 'Допълнително описание (подзаглавие)',
                'type' => 'textarea',
                'rows' => 2
            ]
        ];

        function buildMenuFields(array $baseFields, int $level = 0): array
        {
            $fields = $baseFields;

            if ($level >= 20) {
                return $fields;
            }

            $fields['children'] = [
                'label' => 'Подменю',
                'type' => 'repeater',
                'fields' => buildMenuFields($baseFields, $level + 1)
            ];

            return $fields;
        }

        $menuFields = buildMenuFields($baseFields);

        $items = $menuItems ?? [[]];

        Form::repeater('menu_items', 'Елементи', [
            'value' => $items,
            'fields' => $menuFields,
            'title_field' => 'label',
            'loadUrl' => '/admin/menus/items/' . $menu->id,
            'saveUrl' => '/admin/menus/items/' . $menu->id . '/tree-update'
        ]);
        ?>

    <?php }, isWithBottomMargin: false); ?>

<?php }, ['md' => 2]); ?>

<?php Form::section(title: 'Настройки и локация', slot: function () use ($menu) { ?>

    <?php Form::row(function () use ($menu) {

        $locations = [
            'header' => 'Главна навигация (Header)',
            'footer_main' => 'Футер - Основни линкове',
            'footer_secondary' => 'Футер - Допълнителни линкове',
            'mobile' => 'Мобилно меню'
        ];

        Form::customSelect(
            'options[location]',
            'Позиция в сайта',
            $locations,
            $menu->options['location'] ?? 'header'
        );

    }, ['md' => 2]); ?>

    <?php Form::row(function () use ($menu) {

        Form::toggle('is_active', 'Активно меню', [
            'value' => $menu->is_active ?? true,
            'description' => 'Ако е деактивирано, менюто няма да се визуализира на сайта.'
        ]);

    }, ['md' => 2]); ?>

<?php }); ?>

<?php if (!empty($menu->options['custom_template']))
    View::dispatchOptions($menu, 'template'); ?>

<?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>
