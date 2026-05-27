<?php

namespace Flex\Core\Controllers;

use Flex\Core\Routing\View;

abstract class BaseController
{
    protected function handleToggleStatus(string $modelClass, string $statusField = 'is_active'): void
    {
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'message' => 'Невалидно или липсващо ID.'], 400);
        }

        if (!class_exists($modelClass)) {
            $this->json(['success' => false, 'message' => 'Системна грешка: Моделът не съществува.'], 500);
        }

        $item = $modelClass::find($id);

        if (!$item) {
            $this->json(['success' => false, 'message' => 'Елементът не беше намерен.'], 404);
        }

        try {
            $item->{$statusField} = $item->{$statusField} ? 0 : 1;
            $item->save();

            $this->json([
                'success' => true,
                'message' => 'Статусът беше променен успешно.',
                'new_status' => (bool) $item->{$statusField}
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Възникна грешка при записа в базата данни.'], 500);
        }
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

        $reflection = new \ReflectionClass($this);
        $controllerDir = dirname($reflection->getFileName(), 2);

        $fullViewPath = dirname($controllerDir) . '/views/' . $view->path . '.php';

        if (strpos($controllerDir, 'app' . DIRECTORY_SEPARATOR . 'Controllers') !== false) {
            $fullViewPath = dirname($controllerDir, 2) . '/views/' . $view->path . '.php';
        }

        ob_start();
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            echo "View file not found: " . htmlspecialchars($fullViewPath);
        }
        $content = ob_get_clean();

        $layoutPath = dirname(__DIR__, 2) . "/views/layouts/{$view->layout}.php";

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
            'data'    => $data
        ]);
        
        exit;
    }
}

trait HandlesMedia
{
    public function handleFileUploads(array $currentOptions, string $folder = 'uploads'): array
    {
        $manager = new \Flex\Core\Services\MediaManager();
        $options = $currentOptions;

        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {

                    if (!empty($options[$key])) {
                        $manager->remove($options[$key]);
                    }

                    $path = $manager->upload($file, $folder);
                    if ($path) {
                        $options[$key] = $path;
                    }
                }
            }
        }
        return $options;
    }
}

trait RequestHelper
{
    public function getCheckboxValue(string $key, array|null $data = null): int
    {
        $data = $data ?? $_POST;
        return isset($data[$key]) ? 1 : 0;
    }
}