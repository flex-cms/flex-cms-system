<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Services;

use ArrayObject;
use Flex\Features\Pages\Exceptions\InvalidPageElementException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use Flex\Features\Pages\Repositories\EloquentPageElementRepository;
use Flex\Features\Pages\Services\PageElementService;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

final class PageElementServiceTest extends TestCase
{
    private Capsule $capsule;
    private EloquentPageElementRepository $repository;
    private PageElementService $service;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $schema = $this->capsule->schema();
        $schema->create(
            PagesTables::pages(),
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('slug');
                $table->string('full_slug')->unique();
                $table->unsignedInteger('parent_id')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            }
        );
        $schema->create(
            PagesTables::elements(),
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('page_id');
                $table->unsignedInteger('parent_id')->nullable();
                $table->string('element_type');
                $table->unsignedInteger('position')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();
            }
        );

        $this->page = $this->createPage('About', 'about');
        $this->repository = new EloquentPageElementRepository();
        $this->service = new PageElementService($this->repository);
    }

    protected function tearDown(): void
    {
        $this->capsule->getConnection()->disconnect();
        Model::unsetConnectionResolver();

        parent::tearDown();
    }

    public function testItCreatesAndReturnsNestedElementTree(): void
    {
        $nodes = $this->service->replace($this->page, [
            [
                'type' => 'hero',
                'position' => 2,
                'settings' => ['title' => 'Welcome'],
                'children' => [
                    [
                        'element_type' => 'button',
                        'settings' => ['label' => 'Learn more'],
                    ],
                ],
            ],
            [
                'type' => 'text',
                'position' => 1,
                'settings' => ['content' => 'About us'],
            ],
        ]);

        self::assertCount(2, $nodes);
        self::assertSame('text', $nodes[0]->element->element_type);
        self::assertSame('hero', $nodes[1]->element->element_type);
        self::assertCount(1, $nodes[1]->children);
        self::assertSame(
            $nodes[1]->element->id,
            $nodes[1]->children[0]->element->parent_id
        );
        self::assertInstanceOf(
            ArrayObject::class,
            $nodes[1]->element->settings
        );
        self::assertSame('Welcome', $nodes[1]->element->settings['title']);
    }

    public function testReplaceUpdatesMovesAndDeletesExistingElements(): void
    {
        $nodes = $this->service->replace($this->page, [
            [
                'type' => 'section',
                'children' => [
                    ['type' => 'text', 'settings' => ['content' => 'Old']],
                ],
            ],
            ['type' => 'image'],
        ]);

        $sectionId = $nodes[0]->element->id;
        $textId = $nodes[0]->children[0]->element->id;

        $updated = $this->service->replace($this->page, [
            [
                'id' => $textId,
                'type' => 'text',
                'settings' => ['content' => 'Updated'],
            ],
        ]);

        self::assertCount(1, $updated);
        self::assertSame($textId, $updated[0]->element->id);
        self::assertNull($updated[0]->element->parent_id);
        self::assertSame('Updated', $updated[0]->element->settings['content']);
        self::assertNull(PageElement::query()->find($sectionId));
        self::assertSame(1, PageElement::query()->count());
    }

    public function testReplaceWithEmptyListRemovesAllElements(): void
    {
        $this->service->replace($this->page, [
            ['type' => 'text'],
            ['type' => 'image'],
        ]);

        self::assertSame([], $this->service->replace($this->page, []));
        self::assertSame(0, PageElement::query()->count());
    }

    public function testItRejectsElementOwnedByAnotherPage(): void
    {
        $otherPage = $this->createPage('Other', 'other');
        $foreign = $this->repository->create($otherPage, [
            'parent_id' => null,
            'element_type' => 'text',
            'position' => 0,
            'settings' => [],
        ]);

        $this->expectException(InvalidPageElementException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Page element [%d] does not belong to page [%d].',
                $foreign->id,
                $this->page->id
            )
        );

        $this->service->replace($this->page, [
            ['id' => $foreign->id, 'type' => 'text'],
        ]);
    }

    public function testItRejectsDuplicateElementId(): void
    {
        $nodes = $this->service->replace($this->page, [
            ['type' => 'text'],
        ]);
        $id = $nodes[0]->element->id;

        $this->expectException(InvalidPageElementException::class);

        $this->service->replace($this->page, [
            ['id' => $id, 'type' => 'text'],
            ['id' => $id, 'type' => 'text'],
        ]);
    }

    public function testItEnforcesAllowedElementTypes(): void
    {
        $this->expectException(InvalidPageElementException::class);
        $this->expectExceptionMessage(
            'Page element type [script] is not allowed.'
        );

        $this->service->replace(
            $this->page,
            [['type' => 'script']],
            ['text', 'image']
        );
    }

    public function testItRejectsSettingsThatCannotBeEncoded(): void
    {
        $this->expectException(InvalidPageElementException::class);

        $this->service->replace($this->page, [[
            'type' => 'number',
            'settings' => ['value' => NAN],
        ]]);
    }

    public function testItRemovesOnlyElementOwnedByPage(): void
    {
        $nodes = $this->service->replace($this->page, [
            ['type' => 'text'],
        ]);

        self::assertTrue(
            $this->service->remove($this->page, $nodes[0]->element->id)
        );
        self::assertFalse(
            $this->service->remove($this->page, $nodes[0]->element->id)
        );
    }

    public function testTreeRejectsCyclicStoredHierarchy(): void
    {
        $first = $this->repository->create($this->page, [
            'parent_id' => null,
            'element_type' => 'section',
            'position' => 0,
            'settings' => [],
        ]);
        $second = $this->repository->create($this->page, [
            'parent_id' => $first->id,
            'element_type' => 'section',
            'position' => 0,
            'settings' => [],
        ]);
        PageElement::query()
            ->whereKey($first->id)
            ->update(['parent_id' => $second->id]);

        $this->expectException(InvalidPageElementException::class);

        $this->service->tree($this->page);
    }

    private function createPage(string $name, string $slug): Page
    {
        return Page::query()->create([
            'name' => $name,
            'slug' => $slug,
            'full_slug' => $slug,
            'position' => 0,
            'is_active' => true,
        ]);
    }
}
