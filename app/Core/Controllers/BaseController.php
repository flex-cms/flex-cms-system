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

    protected function render(View $view, bool $return = false): ?string
    {
        return render_view($view, $return);
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
