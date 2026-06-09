<?php

namespace Flex\Core\Traits;

use Illuminate\Database\Eloquent\Model;

trait CrudHelper
{
    public function buildUpdateData(array $post, $model = null, array $rules = ['name', 'slug', 'is_active', 'created_at' => 'default_date']): array
    {
        $data = [];

        foreach ($rules as $field => $callback) {
            if ($callback === 'default_date') {
                $data[$field] = $post[$field] ?? ($model?->$field ?? date('Y-m-d H:i:s'));
                continue;
            }

            if (is_int($field)) {
                $field = $callback;
                $callback = null;
            }

            if (is_callable($callback)) {
                $data[$field] = $callback($post);
            } else {
                $value = $post[$field] ?? null;
                $data[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        return $data;
    }

    public function buildOptions(array $post, array $exclude = []): array
    {
        $defaults = ['submit', '_token', '_method', 'files'];
        $excluded = array_merge($exclude, $defaults);

        return array_diff_key($post, array_flip($excluded));
    }

    public function mergeOptions(array $post, array $currentOptions, array $exclude = []): array
    {
        $defaults = ['submit', '_token', '_method', 'files', 'name', 'slug', 'created_at', 'is_active', 'options'];
        $excluded = array_merge($exclude, $defaults);

        $rawOptions = array_diff_key($post, array_flip($excluded));

        if (isset($post['options']) && is_array($post['options'])) {
            $rawOptions = array_merge($rawOptions, $post['options']);
        }

        return array_merge($currentOptions, $rawOptions);
    }

    public function deleteRecord(string $modelClass)
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;
        $force = (bool) ($data['force'] ?? false);

        if (!is_numeric($id)) {
            return $this->jsonResponse(false, $this->deleteErrorMessage ?? 'Невалидно ID.');
        }

        try {
            $record = $modelClass::findOrFail($id);
            $force ? $record->forceDelete() : $record->delete();

            return $this->jsonResponse(true, $this->deleteSuccessMessage ?? 'Изтрито успешно.');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Грешка: ' . $e->getMessage());
        }
    }

    public function toggleStatus(string $modelClass, string $statusField = 'is_active'): array
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Невалидно или липсващо ID.',
                'code' => 400
            ];
        }

        $item = $modelClass::find($id);
        if (!$item) {
            return [
                'success' => false,
                'message' => 'Елементът не беше намерен.',
                'code' => 404
            ];
        }

        try {
            $item->{$statusField} = !((bool) $item->{$statusField});
            $item->save();

            return [
                'success' => true,
                'message' => 'Статусът беше променен успешно.',
                'new_status' => (bool) $item->{$statusField},
                'item' => $item
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Възникна грешка при записа.',
                'code' => 500
            ];
        }
    }

    protected function getJsonInput(): ?array
    {
        $jsonInput = file_get_contents('php://input');
        return json_decode($jsonInput, true);
    }
}
