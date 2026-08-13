<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Models;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use Flex\Features\Pages\Models\PageOption;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

final class PageModelsTest extends TestCase
{
    public function testModelsUseFeatureTables(): void
    {
        self::assertSame(PagesTables::pages(), (new Page())->getTable());
        self::assertSame(PagesTables::options(), (new PageOption())->getTable());
        self::assertSame(PagesTables::elements(), (new PageElement())->getTable());
    }

    public function testPageCastsCoreAttributes(): void
    {
        $page = new Page([
            'parent_id' => '12',
            'position' => '3',
            'is_active' => 1,
        ]);

        self::assertSame(12, $page->parent_id);
        self::assertSame(3, $page->position);
        self::assertTrue($page->is_active);
    }

    public function testPageReadsLoadedOptionsWithoutDatabaseQuery(): void
    {
        $page = new Page();
        $page->setRelation('pageOptions', new Collection([
            new PageOption([
                'option_key' => 'layout',
                'option_value' => '"wide"',
            ]),
            new PageOption([
                'option_key' => 'metadata',
                'option_value' => '{"robots":"index"}',
            ]),
            new PageOption([
                'option_key' => 'subtitle',
                'option_value' => null,
            ]),
        ]));

        self::assertSame('wide', $page->getOption('layout'));
        self::assertSame(
            ['robots' => 'index'],
            $page->getOption('metadata')
        );
        self::assertNull($page->getOption('subtitle', 'fallback'));
        self::assertSame('fallback', $page->getOption('missing', 'fallback'));
        self::assertTrue($page->hasOption('layout'));
        self::assertFalse($page->hasOption('missing'));
    }

    public function testPageFiltersLoadedRootElementsByType(): void
    {
        $hero = new PageElement(['element_type' => 'hero']);
        $text = new PageElement(['element_type' => 'text']);
        $secondHero = new PageElement(['element_type' => 'hero']);

        $page = new Page();
        $page->setRelation(
            'elements',
            new Collection([$hero, $text, $secondHero])
        );

        self::assertSame($hero, $page->getElement('hero'));
        self::assertSame(
            [$hero, $secondHero],
            $page->getElementsByType('hero')->all()
        );
    }

    public function testPageOptionPreservesNonJsonText(): void
    {
        $option = new PageOption([
            'option_value' => 'Flex CMS',
        ]);

        self::assertSame('Flex CMS', $option->decodedValue());
    }
}
