<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Data;

use ArrayObject;
use Flex\Features\Pages\Models\PageElement;

final readonly class PageElementNode
{
    /** @param list<PageElementNode> $children */
    public function __construct(
        public PageElement $element,
        public array $children = []
    ) {
    }

    /**
     * @return array{element: PageElement, children: list<array>}
     */
    public function toArray(): array
    {
        return [
            'element' => $this->element,
            'children' => array_map(
                static fn (self $child): array => $child->toArray(),
                $this->children
            ),
        ];
    }

    /**
     * @return array{id: int, type: string, position: int, settings: array<string, mixed>, children: list<array>}
     */
    public function toDefinition(): array
    {
        $settings = $this->element->settings;

        return [
            'id' => (int) $this->element->id,
            'type' => $this->element->element_type,
            'position' => (int) $this->element->position,
            'settings' => $settings instanceof ArrayObject
                ? $settings->getArrayCopy()
                : (is_array($settings) ? $settings : []),
            'children' => array_map(
                static fn (self $child): array => $child->toDefinition(),
                $this->children
            ),
        ];
    }
}
