<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Services;

use Flex\Features\Settings\Configuration\SettingsPageConfig;
use Flex\Features\Settings\Data\SettingsPageData;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use InvalidArgumentException;

final class SettingsService
{
    public const DATABASE_GROUP = 'system';

    /**
     * @var array<string, array<string, array{type: string, default: mixed}>>
     */
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
        private readonly SettingRepositoryInterface $settings,
        private readonly SettingsPageConfig $configuration
    ) {
    }

    public function pageData(string $pageGroup): SettingsPageData
    {
        $settings = $this->valuesForPage($pageGroup);
        $label = $this->configuration->label($pageGroup);

        $label ??= $pageGroup;

        return new SettingsPageData(
            title: 'Настройки: ' . $label,
            currentGroup: $pageGroup,
            definedGroups: $this->configuration->groups(),
            settings: $settings,
            languages: $this->configuration->languages(),
            timezones: $this->configuration->timezones(),
            dateFormats: $this->configuration->dateFormats()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function valuesForPage(string $pageGroup): array
    {
        $definitions = $this->definitionsFor($pageGroup);
        $storedValues = $this->settings->valuesForGroup(
            self::DATABASE_GROUP
        );

        $values = [];

        foreach ($definitions as $key => $definition) {
            $values[$key] = array_key_exists($key, $storedValues)
                ? $storedValues[$key]
                : $definition['default'];
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $submittedSettings
     */
    public function updatePage(
        string $pageGroup,
        array $submittedSettings
    ): void {
        $definitions = $this->definitionsFor($pageGroup);
        $normalizedSettings = [];

        foreach ($definitions as $key => $definition) {
            $type = $definition['type'];

            if ($type === 'boolean') {
                $normalizedSettings[$key] = $this->normalizeBoolean(
                    $submittedSettings[$key] ?? false
                );

                continue;
            }

            if (!array_key_exists($key, $submittedSettings)) {
                continue;
            }

            /*
             * Празна SMTP парола означава:
             * запази съществуващата парола.
             */
            if (
                $pageGroup === 'mail'
                && $key === 'smtp_pass'
                && $submittedSettings[$key] === ''
            ) {
                continue;
            }

            $normalizedSettings[$key] = $this->normalizeValue(
                $submittedSettings[$key],
                $type,
                $key
            );
        }

        $this->settings->transaction(
            function () use ($normalizedSettings): void {
                $this->settings->saveMany(
                    $normalizedSettings,
                    self::DATABASE_GROUP
                );
            }
        );
    }

    /**
     * @return array<string, array{type: string, default: mixed}>
     */
    private function definitionsFor(string $pageGroup): array
    {
        if (
            !$this->configuration->hasGroup($pageGroup)
            || !array_key_exists($pageGroup, self::DEFINITIONS)
        ) {
            throw new UnknownSettingsGroupException($pageGroup);
        }

        return self::DEFINITIONS[$pageGroup];
    }

    private function normalizeValue(
        mixed $value,
        string $type,
        string $key
    ): string|int {
        if (is_array($value) || is_object($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] must contain a scalar value.',
                    $key
                )
            );
        }

        return match ($type) {
            'integer' => filter_var(
                $value,
                FILTER_VALIDATE_INT,
                FILTER_NULL_ON_FAILURE
            ) ?? throw new InvalidArgumentException(
                sprintf(
                    'The setting [%s] must contain an integer.',
                    $key
                )
            ),

            default => (string) $value,
        };
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}