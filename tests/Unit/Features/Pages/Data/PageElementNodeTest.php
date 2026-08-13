<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Data;

use Flex\Features\Pages\Data\PageElementNode;
use Flex\Features\Pages\Models\PageElement;
use PHPUnit\Framework\TestCase;

final class PageElementNodeTest extends TestCase
{
    public function testItSerializesBuilderDefinitionRecursively(): void
    {
        $child = $this->element(2, 'text', 0, ['content' => 'Hello']);
        $root = $this->element(1, 'container', 1, ['width' => 'full']);

        $definition = (new PageElementNode(
            $root,
            [new PageElementNode($child)]
        ))->toDefinition();

        self::assertSame(1, $definition['id']);
        self::assertSame('container', $definition['type']);
        self::assertSame(['width' => 'full'], $definition['settings']);
        self::assertSame(2, $definition['children'][0]['id']);
        self::assertSame(
            ['content' => 'Hello'],
            $definition['children'][0]['settings']
        );
    }

    /** @param array<string, mixed> $settings */
    private function element(
        int $id,
        string $type,
        int $position,
        array $settings
    ): PageElement {
        $element = new PageElement();
        $element->setRawAttributes([
            'id' => $id,
            'page_id' => 1,
            'parent_id' => null,
            'element_type' => $type,
            'position' => $position,
            'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
        ], true);

        return $element;
    }
}
