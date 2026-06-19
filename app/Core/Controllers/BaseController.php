<?php

namespace Flex\Core\Controllers;

abstract class BaseController
{
    protected function createButton(string $url, string $label = 'Добави')
    {
        return [
            'label' => $label,
            'url' => $url,
            'icon' => 'fa-plus'
        ];
    }

    protected function jsonResponse(bool $success, string $message = '', array $data = []): void
    {
        header('Content-Type: application/json');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }
}
