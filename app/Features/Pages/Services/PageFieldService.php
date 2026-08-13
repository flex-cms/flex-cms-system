<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Services;

use Flex\Features\Pages\Data\PageFieldData;
use Flex\Features\Pages\Data\PageFieldType;
use Flex\Features\Pages\Exceptions\InvalidPageFieldException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageField;
use Flex\Features\Pages\Repositories\PageFieldRepositoryInterface;
use JsonException;

final readonly class PageFieldService
{
    public function __construct(private PageFieldRepositoryInterface $fields)
    {
    }

    public function paginate(Page $page, array $query): array
    {
        return $this->fields->paginate($page, $query);
    }

    public function findOrFail(Page $page, int $id): PageField
    {
        return $this->fields->findOrFail($page, $id);
    }

    public function create(Page $page, array $input): PageField
    {
        $data = $this->data($input);
        $this->assertUniqueKey($page, $data->key);

        return $this->fields->create($page, $data->toPersistenceArray());
    }

    public function update(Page $page, int $id, array $input): PageField
    {
        $field = $this->findOrFail($page, $id);
        $data = $this->data($input);
        $this->assertUniqueKey($page, $data->key, $id);

        return $this->fields->update($field, $data->toPersistenceArray());
    }

    public function delete(Page $page, int $id): void
    {
        $this->fields->delete($this->findOrFail($page, $id));
    }

    public function import(Page $page, string $json): int
    {
        try {
            $definitions = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidPageFieldException('JSON конфигурацията е невалидна.', previous: $exception);
        }

        if (!is_array($definitions) || !array_is_list($definitions)) {
            throw new InvalidPageFieldException('JSON конфигурацията трябва да бъде списък от полета.');
        }

        $data = [];
        $keys = [];

        foreach ($definitions as $definition) {
            if (!is_array($definition)) {
                throw new InvalidPageFieldException('Всяко поле в JSON конфигурацията трябва да бъде обект.');
            }

            $field = $this->data($definition);

            if (isset($keys[$field->key])) {
                throw new InvalidPageFieldException(sprintf('Ключът [%s] се среща повече от веднъж.', $field->key));
            }

            $keys[$field->key] = true;
            $data[] = $field;
        }

        $this->fields->transaction(function () use ($page, $data): void {
            $this->fields->deleteAll($page);

            foreach ($data as $field) {
                $this->fields->create($page, $field->toPersistenceArray());
            }
        });

        return count($data);
    }

    private function data(array $input): PageFieldData
    {
        $type = PageFieldType::tryFrom(trim((string) ($input['type'] ?? '')));
        $label = trim((string) ($input['label'] ?? ''));
        $key = trim((string) ($input['key'] ?? $input['field_key'] ?? ''));
        $group = trim((string) ($input['group'] ?? $input['field_group'] ?? 'general'));
        $order = filter_var($input['order'] ?? $input['position'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $hint = trim((string) ($input['hint'] ?? ''));

        if ($type === null) {
            throw new InvalidPageFieldException('Избраният тип поле е невалиден.');
        }
        if ($label === '' || mb_strlen($label) > 255) {
            throw new InvalidPageFieldException('Етикетът е задължителен и може да съдържа до 255 символа.');
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $key) || strlen($key) > 100) {
            throw new InvalidPageFieldException('Ключът трябва да започва с малка латинска буква и да съдържа само букви, цифри и долна черта.');
        }
        if (!preg_match('/^[a-z][a-z0-9_.-]*$/', $group) || strlen($group) > 100) {
            throw new InvalidPageFieldException('Групата е невалидна.');
        }
        if ($order === null || $order < 0) {
            throw new InvalidPageFieldException('Редът трябва да бъде неотрицателно цяло число.');
        }

        return new PageFieldData($type, $label, $key, $group, $order, $hint !== '' ? $hint : null, is_array($input['settings'] ?? null) ? $input['settings'] : []);
    }

    private function assertUniqueKey(Page $page, string $key, ?int $exceptId = null): void
    {
        if ($this->fields->keyExists($page, $key, $exceptId)) {
            throw new InvalidPageFieldException(sprintf('Поле с ключ [%s] вече съществува.', $key));
        }
    }
}
