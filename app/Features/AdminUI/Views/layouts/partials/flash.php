<?php

declare(strict_types=1);
use Flex\Core\Helpers\Flash;

$flashTypes = [
    'success' => ['label' => 'Успешно', 'icon' => 'fa-circle-check'],
    'error' => ['label' => 'Грешка', 'icon' => 'fa-circle-xmark'],
    'warning' => ['label' => 'Предупреждение', 'icon' => 'fa-triangle-exclamation'],
    'info' => ['label' => 'Информация', 'icon' => 'fa-circle-info'],
];

$flashMessages = Flash::pull();

foreach ($flashMessages as &$flash) {
    $definition = $flashTypes[$flash['type']] ?? $flashTypes['info'];
    $flash = [...$flash, ...$definition];
}

unset($flash);
