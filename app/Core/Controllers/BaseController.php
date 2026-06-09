<?php

namespace Flex\Core\Controllers;

use Flex\Core\Routing\View;

abstract class BaseController
{
    protected function renderAdmin(string $view, array $data = [])
    {
        $viewName = '/admin/' . $view;
        $this->render(View::make($viewName, $data, 'admin'));
    }

    protected function createButton(string $url, string $label = 'Добави')
    {
        return [
            'label' => $label,
            'url' => $url,
            'icon' => 'fa-plus'
        ];
    }

    public function callAction(string $method, array $parameters = [])
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        }

        throw new \Exception("Методът {$method} не съществува в " . get_class($this));
    }

    public function render(View $view): void
    {
        extract($view->data);

        if ($view->source === 'theme') {
            $fullViewPath = dirname(__DIR__, 3) . '/themes/' . ACTIVE_THEME . '/views/' . $view->path . '.php';
            $layoutPath = dirname(__DIR__, 2) . "/views/layouts/{$view->layout}.php";
        } else {
            $fullViewPath = dirname(__DIR__, 2) . '/views/' . $view->path . '.php';
            $layoutPath = dirname(__DIR__, 2) . "/views/layouts/{$view->layout}.php";
        }

        ob_start();
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            die("View not found: " . htmlspecialchars($fullViewPath));
        }
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }

        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
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