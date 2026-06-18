<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Link;
?>

<div class="text-center space-y-8">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full shadow-inner">
        <i class="fas fa-check text-4xl"></i>
    </div>

    <div class="space-y-2">
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Инсталацията е успешна!</h2>
        <p class="text-gray-600 text-lg">Flex CMS е напълно конфигуриран и готов за работа.</p>
    </div>

    <div class="bg-white border-y border-gray-200 p-5 rounded-2xl shadow-sm text-left">
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Данни за администраторски достъп</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600 font-medium">Email:</span>
                <span class="font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">
                    <?= $admin_email ?>
                </span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600 font-medium">Парола:</span>
                <span class="font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">
                    <?= $admin_password ?>
                </span>
            </div>
        </div>

        <?php Alert::make('Запазете тези данни на сигурно място! Поради съображения за сигурност паролата не се показва повторно.')->warning() ?>
    </div>

    <?= Link::make('/admin', 'Преминаване към таблото') ?>
</div>
