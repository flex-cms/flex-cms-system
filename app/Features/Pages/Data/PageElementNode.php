<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Data;

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
}
