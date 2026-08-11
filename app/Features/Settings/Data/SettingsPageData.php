<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Data;

final readonly class SettingsPageData
{
    public function __construct(
        public string $group,
        public string $storageGroup,
        public string $title,
        public string $label,
        public ?string $description,
        public array $values,
        public array $languages = [],
        public array $timezones = [],
        public array $dateFormats = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'group' => $this->group,
            'storageGroup' => $this->storageGroup,
            'title' => $this->title,
            'label' => $this->label,
            'description' => $this->description,
            'values' => $this->values,
            'languages' => $this->languages,
            'timezones' => $this->timezones,
            'dateFormats' => $this->dateFormats,
        ];
    }
}
