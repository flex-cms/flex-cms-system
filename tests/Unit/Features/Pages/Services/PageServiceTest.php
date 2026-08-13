<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Services;

use Flex\Features\Pages\Exceptions\InvalidPageDataException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\EloquentPageRepository;
use Flex\Features\Pages\Services\PageService;
use Flex\Features\Pages\Services\PageTreeService;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

final class PageServiceTest extends TestCase
{
    private Capsule $capsule;
    private EloquentPageRepository $repository;
    private PageService $service;

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
            PagesTables::options(),
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('page_id');
                $table->string('option_key');
                $table->text('option_value')->nullable();
                $table->timestamps();
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

        $this->repository = new EloquentPageRepository();
        $tree = new PageTreeService($this->repository);
        $this->service = new PageService($this->repository, $tree);
    }

    protected function tearDown(): void
    {
        $this->capsule->getConnection()->disconnect();
        Model::unsetConnectionResolver();

        parent::tearDown();
    }

    public function testItCreatesRootAndNestedPages(): void
    {
        $root = $this->service->create([
            'name' => ' Services ',
            'position' => '2',
            'is_active' => '1',
        ]);
        $child = $this->service->create([
            'name' => 'Web Development',
            'slug' => 'web',
            'parent_id' => (string) $root->id,
        ]);

        self::assertSame('Services', $root->name);
        self::assertSame('services', $root->full_slug);
        self::assertSame(2, $root->position);
        self::assertTrue($root->is_active);
        self::assertSame('services/web', $child->full_slug);
        self::assertSame($root->id, $child->parent_id);
    }

    public function testItUpdatesDescendantPathsWhenParentPathChanges(): void
    {
        $root = $this->create('Root');
        $child = $this->create('Child', $root->id);
        $grandchild = $this->create('Grandchild', $child->id);

        $updated = $this->service->update($root->id, [
            'slug' => 'new-root',
        ]);

        self::assertSame('new-root', $updated->full_slug);
        self::assertSame(
            'new-root/child',
            $this->repository->findOrFail($child->id)->full_slug
        );
        self::assertSame(
            'new-root/child/grandchild',
            $this->repository->findOrFail($grandchild->id)->full_slug
        );
    }

    public function testItDeletesRestoresAndTogglesPage(): void
    {
        $page = $this->create('About');

        $this->service->delete($page->id);
        $deleted = $this->repository->findOrFail($page->id);

        self::assertTrue($deleted->trashed());
        self::assertFalse($deleted->is_active);

        $restored = $this->service->restore($page->id);
        self::assertFalse($restored->trashed());
        self::assertFalse($restored->is_active);

        $toggled = $this->service->toggle($page->id);
        self::assertTrue($toggled->is_active);
    }

    public function testForceDeleteRerootsChildrenAndUpdatesDescendants(): void
    {
        $root = $this->create('Root');
        $child = $this->create('Child', $root->id);
        $grandchild = $this->create('Grandchild', $child->id);

        $this->service->delete($root->id);
        $this->service->forceDelete($root->id);

        self::assertNull($this->repository->find($root->id));

        $child = $this->repository->findOrFail($child->id);
        self::assertNull($child->parent_id);
        self::assertSame('child', $child->full_slug);
        self::assertSame(
            'child/grandchild',
            $this->repository->findOrFail($grandchild->id)->full_slug
        );
    }

    public function testItReordersPagesInTransaction(): void
    {
        $first = $this->create('First');
        $second = $this->create('Second');

        $this->service->reorder([
            ['id' => (string) $first->id, 'position' => '4'],
            ['id' => $second->id, 'position' => 3],
        ]);

        self::assertSame(4, $first->fresh()->position);
        self::assertSame(3, $second->fresh()->position);
    }

    public function testItReturnsFlattenedFilteredTree(): void
    {
        $root = $this->create('Root');
        $child = $this->create('Child', $root->id);
        $this->service->setActive($child->id, false);

        $items = $this->service->tree(status: 'active');

        self::assertCount(1, $items);
        self::assertSame($root->id, $items[0]->page->id);
    }

    public function testItRunsBulkLifecycleActions(): void
    {
        $first = $this->create('First');
        $second = $this->create('Second');

        $deactivated = $this->service->bulk([
            'action' => 'deactivate',
            'ids' => [(string) $first->id, $second->id, $second->id],
        ]);

        self::assertSame(2, $deactivated['affected']);
        self::assertFalse($first->fresh()->is_active);
        self::assertFalse($second->fresh()->is_active);

        $activated = $this->service->bulk([
            'action' => 'activate',
            'ids' => [$first->id, $second->id],
        ]);

        self::assertSame(2, $activated['affected']);
        self::assertTrue($first->fresh()->is_active);
        self::assertTrue($second->fresh()->is_active);

        $trashed = $this->service->bulk([
            'action' => 'trash',
            'ids' => [$first->id, $second->id],
        ]);

        self::assertSame(2, $trashed['affected']);
        self::assertTrue($this->repository->findOrFail($first->id)->trashed());
        self::assertTrue($this->repository->findOrFail($second->id)->trashed());

        $restored = $this->service->bulk([
            'action' => 'restore',
            'ids' => [$first->id, $second->id],
        ]);

        self::assertSame(2, $restored['affected']);
        self::assertFalse($this->repository->findOrFail($first->id)->trashed());
        self::assertFalse($this->repository->findOrFail($second->id)->trashed());
    }

    public function testBulkRejectsUnknownActionAndInvalidIds(): void
    {
        $this->expectException(InvalidPageDataException::class);

        $this->service->bulk([
            'action' => 'unknown',
            'ids' => [0, 'invalid'],
        ]);
    }

    public function testItRejectsInvalidInputBeforeWriting(): void
    {
        $this->expectException(InvalidPageDataException::class);
        $this->expectExceptionMessage('A page name cannot be empty.');

        $this->service->create(['name' => '   ']);
    }

    public function testItRejectsDuplicateReorderEntries(): void
    {
        $page = $this->create('Page');

        $this->expectException(InvalidPageDataException::class);

        $this->service->reorder([
            ['id' => $page->id, 'position' => 1],
            ['id' => $page->id, 'position' => 2],
        ]);
    }

    private function create(string $name, ?int $parentId = null): Page
    {
        return $this->service->create([
            'name' => $name,
            'parent_id' => $parentId,
        ]);
    }
}
