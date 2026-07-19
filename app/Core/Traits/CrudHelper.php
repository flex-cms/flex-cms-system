<?php

namespace Flex\Core\Traits;

use InvalidArgumentException;

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

    public function mergeOptions(array $post, array $currentOptions, array $exclude = []): array
    {
        $defaults = ['submit', '_token', '_method', 'files', 'name', 'slug', 'created_at', 'is_active', 'position', 'options'];
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
            throw new InvalidArgumentException('Невалидно ID за преместване в кошчето.');
        }

        $record = $modelClass::findOrFail($id);

        return $force ? $record->forceDelete() : $record->delete();
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

        $item->{$statusField} = !((bool) $item->{$statusField});
        $item->save();

        return [
            'success' => true,
            'message' => 'Статусът беше променен успешно.',
            'new_status' => (bool) $item->{$statusField},
            'item' => $item
        ];
    }

    public function restoreRecord(string $modelClass): void
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;

        if (!is_numeric($id)) {
            throw new InvalidArgumentException('Невалидно ID за възстановяване.');
        }

        $record = $modelClass::onlyTrashed()->findOrFail($id);
        $record->restore();

        if (isset($record->is_active)) {
            $record->is_active = 0;
            $record->save();
        } elseif (isset($record->status)) {
            $record->status = 0;
            $record->save();
        }
    }

    public function forceDeleteRecord(string $modelClass)
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;

        if (!is_numeric($id)) {
            throw new InvalidArgumentException('Невалидно ID за перманентно изтриване.');
        }

        $record = $modelClass::withTrashed()->findOrFail($id);
        $record->forceDelete();
    }

    protected function getJsonInput(): ?array
    {
        $jsonInput = file_get_contents('php://input');
        return json_decode($jsonInput, true);
    }

    protected function updatePositionMethod(
        string $modelClass,
        string $sort_order = 'order',
        ?string $groupColumn = null
    ): void {
        $data = $this->getJsonInput();

        $id = $data['id'] ?? null;
        $order = $data['order'] ?? null;

        if (!is_numeric($id) || !is_numeric($order)) {
            throw new InvalidArgumentException(
                'Невалидни данни за позиция.'
            );
        }

        $model = $modelClass::findOrFail((int) $id);

        $model->{$sort_order} = (int) $order;
        $model->save();

        if (!$groupColumn) {
            return;
        }

        $query = $modelClass::where(
            $groupColumn,
            $model->{$groupColumn}
        );

        $items = $query
            ->orderBy($sort_order)
            ->get();

        foreach ($items as $index => $item) {
            if ($item->id === $model->id) {
                continue;
            }

            $item->{$sort_order} = $index;
            $item->save();
        }
    }

    protected function updateTreeAndOrder(
        string $modelClass,
        string $parentColumn = 'parent_id',
        string $sort_order = 'order',
        ?string $groupColumn = null
    ): void {
        $data = $this->getJsonInput();

        if (!isset($data['id'])) {
            throw new InvalidArgumentException('Липсва ID.');
        }

        $model = $modelClass::findOrFail((int) $data['id']);
        $oldParentId = $model->{$parentColumn};
        $newParentId = $data['parent_id'] ?? null;

        if ($newParentId === "0" || $newParentId === 0) {
            $newParentId = null;
        }

        $newOrder = (int) ($data['order'] ?? 0);
        $model->{$parentColumn} = $newParentId;

        $model->save();

        $query = $modelClass::where(
            $parentColumn,
            $newParentId
        );

        if ($groupColumn) {
            $query->where(
                $groupColumn,
                $model->{$groupColumn}
            );
        }

        $siblings = $query
            ->orderBy($sort_order)
            ->get();

        $siblings = $siblings
            ->reject(fn($item) => $item->id === $model->id)
            ->values();

        $siblings->splice(
            $newOrder,
            0,
            [$model]
        );

        foreach ($siblings as $index => $item) {
            $item->{$sort_order} = $index;
            $item->save();
        }

        if ($oldParentId != $newParentId) {

            $oldQuery = $modelClass::where(
                $parentColumn,
                $oldParentId
            );

            if ($groupColumn) {
                $oldQuery->where(
                    $groupColumn,
                    $model->{$groupColumn}
                );
            }

            foreach (
                $oldQuery
                    ->orderBy($sort_order)
                    ->get()
                as $index => $item
            ) {
                $item->{$sort_order} = $index;
                $item->save();
            }
        }
    }
}