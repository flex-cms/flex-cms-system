<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Services;

use Flex\Features\Settings\Data\SettingsPageData;
use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Support\SettingsOptions;
use InvalidArgumentException;

final class SettingsService
{
    public const DATABASE_GROUP = 'general';

    private const DEFINITIONS = [
        'general' => [
            'site_name' => [
                'type' => 'string',
                'default' => 'Flex CMS',
            ],
            'admin_email' => [
                'type' => 'string',
                'default' => '',
            ],
            'site_url' => [
                'type' => 'string',
                'default' => '',
            ],
            'site_description' => [
                'type' => 'string',
                'default' => '',
            ],
            'timezone' => [
                'type' => 'string',
                'default' => 'Europe/Sofia',
            ],
            'date_format' => [
                'type' => 'string',
                'default' => 'd.m.Y',
            ],
            'debug_mode' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'enable_multilang' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'site_default_lang' => [
                'type' => 'string',
                'default' => 'bg',
            ],
            'admin_default_lang' => [
                'type' => 'string',
                'default' => 'bg',
            ],
        ],

        'mail' => [
            'smtp_host' => [
                'type' => 'string',
                'default' => '',
            ],
            'smtp_port' => [
                'type' => 'integer',
                'default' => 587,
            ],
            'smtp_user' => [
                'type' => 'string',
                'default' => '',
            ],
            'smtp_pass' => [
                'type' => 'string',
                'default' => '',
                'preserve_on_empty' => true,
            ],
            'smtp_encryption' => [
                'type' => 'string',
                'default' => 'tls',
            ],
            'from_email' => [
                'type' => 'string',
                'default' => '',
            ],
        ],

        'media' => [
            'media_use_date_folders' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'media_keep_original_name' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'media_max_size' => [
                'type' => 'integer',
                'default' => 5,
            ],
            'media_allowed_extensions' => [
                'type' => 'string',
                'default' => 'jpg,png,webp',
            ],
        ],
    ];

    public function __construct(
        private readonly SettingRepositoryInterface $settings
    ) {
    }

    public function pageData(
        string $pageGroup
    ): SettingsPageData {
        $this->ensurePageExists(
            $pageGroup
        );

        return new SettingsPageData(
            group: $pageGroup,
            storageGroup: self::DATABASE_GROUP,
            title: $this->titleFor(
                $pageGroup
            ),
            label: $this->labelFor(
                $pageGroup
            ),
            description: null,
            values: $this->valuesForPage(
                $pageGroup
            ),
            languages:
                SettingsOptions::languages(),
            timezones:
                SettingsOptions::timezones(),
            dateFormats:
                SettingsOptions::dateFormats()
        );
    }

    public function valuesForPage(
        string $pageGroup
    ): array {
        $definitions =
            $this->definitionsFor(
                $pageGroup
            );

        $storedValues =
            $this->settings->valuesForGroup(
                self::DATABASE_GROUP
            );

        $values = [];

        foreach (
            $definitions
            as $key => $definition
        ) {
            $values[$key] =
                array_key_exists(
                    $key,
                    $storedValues
                )
                    ? $storedValues[$key]
                    : $definition['default'];
        }

        return $values;
    }

    public function dateRuntimeConfig(): array
    {
        $storedValues =
            $this->settings->valuesForGroup(
                self::DATABASE_GROUP
            );

        $dateFormat = (string) (
            $storedValues['date_format']
            ?? SettingsOptions::defaultDateFormat()
        );

        if (!SettingsOptions::hasDateFormat($dateFormat)) {
            $dateFormat = SettingsOptions::defaultDateFormat();
        }

        $timezone = (string) (
            $storedValues['timezone']
            ?? SettingsOptions::defaultTimezone()
        );

        if (!SettingsOptions::hasTimezone($timezone)) {
            $timezone = SettingsOptions::defaultTimezone();
        }

        $locale = (string) (
            $storedValues['admin_default_lang']
            ?? SettingsOptions::defaultLocale()
        );

        if (!SettingsOptions::hasLanguage($locale)) {
            $locale = SettingsOptions::defaultLocale();
        }

        return [
            'date_format' => $dateFormat,
            'time_format' => SettingsOptions::defaultTimeFormat(),
            'timezone' => $timezone,
            'locale' => $locale,
        ];
    }

    public function updatePage(
        string $pageGroup,
        array $submittedSettings
    ): void {
        $definitions =
            $this->definitionsFor(
                $pageGroup
            );

        $normalizedSettings = [];

        foreach (
            $definitions
            as $key => $definition
        ) {
            $type =
                $definition['type'];

            if ($type === 'boolean') {
                $normalizedSettings[$key] =
                    $this->normalizeBoolean(
                        $submittedSettings[$key]
                        ?? false
                    );

                continue;
            }

            if (
                !array_key_exists(
                    $key,
                    $submittedSettings
                )
            ) {
                continue;
            }

            if (
                ($definition['preserve_on_empty']
                    ?? false) === true
                && (
                    $submittedSettings[$key] === ''
                    || $submittedSettings[$key] === null
                )
            ) {
                continue;
            }

            $normalizedSettings[$key] =
                $this->validateOption(
                    $key,
                    $this->normalizeValue(
                    $submittedSettings[$key],
                    $type,
                    $key
                    )
                );
        }

        if ($normalizedSettings === []) {
            return;
        }

        $this->settings->transaction(
            function () use (
                $normalizedSettings
            ): void {
                $this->settings->saveMany(
                    $normalizedSettings,
                    self::DATABASE_GROUP
                );
            }
        );
    }

    private function definitionsFor(
        string $pageGroup
    ): array {
        if (
            !array_key_exists(
                $pageGroup,
                self::DEFINITIONS
            )
        ) {
            throw new UnknownSettingsGroupException(
                $pageGroup
            );
        }

        return self::DEFINITIONS[
            $pageGroup
        ];
    }

    private function ensurePageExists(
        string $pageGroup
    ): void {
        $this->definitionsFor(
            $pageGroup
        );
    }

    private function labelFor(
        string $pageGroup
    ): string {
        return match ($pageGroup) {
            'general' =>
                'Общи настройки',

            'mail' =>
                'Имейл настройки',

            'media' =>
                'Медийни настройки',

            default =>
                $pageGroup,
        };
    }

    private function titleFor(
        string $pageGroup
    ): string {
        return 'Настройки: '
            . $this->labelFor(
                $pageGroup
            );
    }

    private function normalizeValue(
        mixed $value,
        string $type,
        string $key
    ): string|int|float {
        if (
            is_array($value)
            || is_object($value)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] must contain a scalar value.',
                    $key
                )
            );
        }

        return match ($type) {
            'integer' =>
                $this->normalizeInteger(
                    $value,
                    $key
                ),

            'float' =>
                $this->normalizeFloat(
                    $value,
                    $key
                ),

            default =>
                (string) $value,
        };
    }

    private function normalizeInteger(
        mixed $value,
        string $key
    ): int {
        $normalized =
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                FILTER_NULL_ON_FAILURE
            );

        if ($normalized === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] must contain an integer.',
                    $key
                )
            );
        }

        return $normalized;
    }

    private function normalizeFloat(
        mixed $value,
        string $key
    ): float {
        if (
            !is_scalar($value)
            || !is_numeric($value)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] must contain a numeric value.',
                    $key
                )
            );
        }

        return (float) $value;
    }

    private function normalizeBoolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function validateOption(
        string $key,
        string|int|float $value
    ): string|int|float {
        $valid = match ($key) {
            'date_format' =>
                SettingsOptions::hasDateFormat(
                    (string) $value
                ),

            'timezone' =>
                SettingsOptions::hasTimezone(
                    (string) $value
                ),

            'site_default_lang',
            'admin_default_lang' =>
                SettingsOptions::hasLanguage(
                    (string) $value
                ),

            default => true,
        };

        if (!$valid) {
            throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] contains an unsupported value.',
                    $key
                )
            );
        }

        return $value;
    }
}
