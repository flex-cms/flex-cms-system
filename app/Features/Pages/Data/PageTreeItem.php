<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Data;

use Flex\Features\Pages\Models\Page;

final readonly class PageTreeItem
{
    public function __construct(
        public Page $page,
        public int $level
    ) {
    }

    public function displayName(): string
    {
        return str_repeat('— ', $this->level) . $this->page->name;
    }

    /** @return array{page: Page, level: int, display_name: string} */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'level' => $this->level,
            'display_name' => $this->displayName(),
        ];
    }
}
