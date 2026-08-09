<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Data;

final readonly class SettingsPageData
{
    /**
     * @param array<string, array{label?: string, url?: string}|string> $definedGroups
     * @param array<string, mixed> $settings
     * @param array<string, string> $languages
     * @param array<string, string> $timezones
     * @param array<string, string> $dateFormats
     */
    public function __construct(
        public string $title,
        public string $currentGroup,
        public array $definedGroups,
        public array $settings,
        public array $languages,
        public array $timezones,
        public array $dateFormats
    ) {
    }

    /**
     * Подготвя данните за подаване към ViewRenderer.
     *
     * @return array{
     *     title: string,
     *     currentGroup: string,
     *     group: string,
     *     definedGroups: array<string, array{label?: string, url?: string}|string>,
     *     settings: array<string, mixed>,
     *     languages: array<string, string>,
     *     timezones: array<string, string>,
     *     dateFormats: array<string, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'currentGroup' => $this->currentGroup,
            'group' => $this->currentGroup,
            'definedGroups' => $this->definedGroups,
            'settings' => $this->settings,
            'languages' => $this->languages,
            'timezones' => $this->timezones,
            'dateFormats' => $this->dateFormats,
        ];
    }
}