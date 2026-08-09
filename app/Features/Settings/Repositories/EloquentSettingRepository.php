<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Repositories;

use Flex\Features\Settings\Models\Setting;

final class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function find(
        string $key,
        ?string $group = null
    ): ?Setting {
        $query = Setting::query()->where('key', $key);

        if ($group !== null) {
            $query->where('group', $group);
        }

        return $query->first();
    }

    public function value(
        string $key,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $setting = $this->find($key, $group);

        if ($setting === null) {
            return $default;
        }

        return $setting->typedValue();
    }

    public function valuesForGroup(string $group): array
    {
        $settings = Setting::query()
            ->where('group', $group)
            ->orderBy('key')
            ->get();

        $values = [];

        foreach ($settings as $setting) {
            $values[$setting->key] = $setting->typedValue();
        }

        return $values;
    }

    public function keysByType(
        string $group,
        string $type
    ): array {
        return Setting::query()
            ->where('group', $group)
            ->where('type', $type)
            ->orderBy('key')
            ->pluck('key')
            ->map(
                static fn (mixed $key): string => (string) $key
            )
            ->values()
            ->all();
    }

    public function save(
        string $key,
        mixed $value,
        string $group
    ): Setting {
        $type = Setting::detectType($value);
        $serializedValue = Setting::serializeValue($value);

        return Setting::query()->updateOrCreate(
            [
                'key' => $key,
                'group' => $group,
            ],
            [
                'value' => $serializedValue,
                'type' => $type,
            ]
        );
    }

    public function saveMany(
        array $settings,
        string $group
    ): void {
        foreach ($settings as $key => $value) {
            $this->save(
                (string) $key,
                $value,
                $group
            );
        }
    }

    public function transaction(callable $callback): mixed
    {
        return (new Setting())
            ->getConnection()
            ->transaction($callback);
    }
}
