<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Data;

final readonly class PageFieldData
{
    /** @param array<string, mixed> $settings */
    public function __construct(
        public PageFieldType $type,
        public string $label,
        public string $key,
        public string $group,
        public int $order,
        public ?string $hint = null,
        public array $settings = []
    ) {
    }

    /** @return array<string, mixed> */
    public function toPersistenceArray(): array
    {
        return [
            'type' => $this->type->value,
            'label' => $this->label,
            'field_key' => $this->key,
            'field_group' => $this->group,
            'position' => $this->order,
            'hint' => $this->hint,
            'settings' => $this->settings,
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'label' => $this->label,
            'key' => $this->key,
            'group' => $this->group,
            'order' => $this->order,
            'hint' => $this->hint,
            'settings' => $this->settings,
        ];
    }
}
