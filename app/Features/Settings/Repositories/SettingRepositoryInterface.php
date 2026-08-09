<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Repositories;

use Flex\Features\Settings\Models\Setting;

interface SettingRepositoryInterface
{
    public function find(
        string $key,
        ?string $group = null
    ): ?Setting;

    public function value(
        string $key,
        mixed $default = null,
        ?string $group = null
    ): mixed;

    /**
     * Връща настройките като асоциативен масив:
     *
     * [
     *     'site_name' => 'Flex CMS',
     *     'debug_mode' => false,
     * ]
     *
     * @return array<string, mixed>
     */
    public function valuesForGroup(string $group): array;

    /**
     * Връща ключовете на настройките от даден тип.
     *
     * Използва се основно за unchecked boolean полета.
     *
     * @return list<string>
     */
    public function keysByType(
        string $group,
        string $type
    ): array;

    public function save(
        string $key,
        mixed $value,
        string $group
    ): Setting;

    /**
     * @param array<string, mixed> $settings
     */
    public function saveMany(
        array $settings,
        string $group
    ): void;

    /**
     * Изпълнява операцията в database transaction.
     */
    public function transaction(callable $callback): mixed;
}