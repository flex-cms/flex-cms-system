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

    public function value(
        string $key,
        mixed $default = null
    ): mixed {
        return array_key_exists(
            $key,
            $this->values
        )
            ? $this->values[$key]
            : $default;
    }

    public function field(
        string $key
    ): ?array {
        $field = $this->fields[$key]
            ?? null;

        return is_array($field)
            ? $field
            : null;
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
