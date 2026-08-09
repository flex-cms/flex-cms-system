<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Configuration;

use Closure;
use DateTimeZone;

final class SettingsPageConfig
{
    /**
     * @var Closure(string, mixed): mixed
     */
    private Closure $configurationReader;

    public function __construct(?callable $configurationReader = null)
    {
        $this->configurationReader = $configurationReader !== null
            ? Closure::fromCallable($configurationReader)
            : static function (string $path, mixed $default = null): mixed {
                if (!function_exists('core_info')) {
                    return $default;
                }

                return \core_info($path, $default);
            };
    }

    /**
     * @return array<string, array{label?: string, url?: string}|string>
     */
    public function groups(): array
    {
        $groups = $this->read(
            'settings_options.settings_page_groups',
            []
        );

        return is_array($groups) ? $groups : [];
    }

    public function hasGroup(string $group): bool
    {
        return array_key_exists($group, $this->groups());
    }

    /**
     * @return array{label?: string, url?: string}|string|null
     */
    public function group(string $group): array|string|null
    {
        return $this->groups()[$group] ?? null;
    }

    public function label(string $group): ?string
    {
        $configuration = $this->group($group);

        if ($configuration === null) {
            return null;
        }

        if (is_string($configuration)) {
            return $configuration;
        }

        $label = $configuration['label'] ?? $group;

        return is_string($label) && $label !== ''
            ? $label
            : $group;
    }

    public function url(string $group): ?string
    {
        $configuration = $this->group($group);

        if (!is_array($configuration)) {
            return null;
        }

        $url = $configuration['url'] ?? null;

        return is_string($url) && $url !== ''
            ? $url
            : null;
    }

    /**
     * @return array<string, string>
     */
    public function languages(): array
    {
        return $this->stringOptions('languages');
    }

    /**
     * @return array<string, string>
     */
    public function dateFormats(): array
    {
        return $this->stringOptions('date_formats');
    }

    /**
     * @return array<string, string>
     */
    public function timezones(): array
    {
        $timezones = [];

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $timezones[$timezone] = str_replace(
                '_',
                ' ',
                basename($timezone)
            );
        }

        asort($timezones, SORT_NATURAL | SORT_FLAG_CASE);

        return $timezones;
    }

    /**
     * @return array<string, string>
     */
    private function stringOptions(string $path): array
    {
        $options = $this->read($path, []);

        if (!is_array($options)) {
            return [];
        }

        $result = [];

        foreach ($options as $value => $label) {
            if (is_string($value) && is_string($label)) {
                $result[$value] = $label;
            }
        }

        return $result;
    }

    private function read(string $path, mixed $default = null): mixed
    {
        return ($this->configurationReader)($path, $default);
    }
}