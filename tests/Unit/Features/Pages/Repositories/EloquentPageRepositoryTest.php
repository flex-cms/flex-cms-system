<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\EloquentPageRepository;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

final class EloquentPageRepositoryTest extends TestCase
{
    private Capsule $capsule;
    private EloquentPageRepository $repository;

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
    }

    protected function tearDown(): void
    {
        $this->capsule->getConnection()->disconnect();
        Model::unsetConnectionResolver();

        parent::tearDown();
    }

    public function testItImplementsRepositoryContract(): void
    {
        self::assertInstanceOf(
            PageRepositoryInterface::class,
            $this->repository
        );
    }

    public function testItCreatesUpdatesAndFindsPage(): void
    {
        $page = $this->repository->create([
            'name' => 'About',
            'slug' => 'about',
            'full_slug' => 'about',
            'position' => 2,
            'is_active' => true,
        ]);

        $updated = $this->repository->update($page, [
            'name' => 'About us',
        ]);

        self::assertSame('About us', $updated->name);
        self::assertSame(
            $updated->id,
            $this->repository->findByFullSlug('about')?->id
        );
        self::assertSame(
            $updated->id,
            $this->repository->findOrFail($updated->id)->id
        );
    }

    public function testItChecksSlugWithinParentScope(): void
    {
        $root = $this->createPage('Services', 'services', 'services');
        $child = $this->createPage('Web', 'web', 'services/web', $root->id);

        self::assertTrue($this->repository->slugExists('web', $root->id));
        self::assertFalse($this->repository->slugExists('web'));
        self::assertFalse(
            $this->repository->slugExists('web', $root->id, $child->id)
        );
    }

    public function testSoftDeletedPageStillReservesItsSiblingSlug(): void
    {
        $page = $this->createPage('About', 'about', 'about');
        $this->repository->delete($page);

        self::assertTrue($this->repository->slugExists('about'));
    }

    public function testItFiltersAndOrdersPages(): void
    {
        $second = $this->createPage('Second', 'second', 'second', null, 2);
        $first = $this->createPage('First', 'first', 'first', null, 1);
        $this->createPage('Hidden', 'hidden', 'hidden', null, 0, false);

        self::assertSame(
            [$first->id, $second->id],
            $this->repository
                ->all(status: 'active')
                ->pluck('id')
                ->all()
        );

        self::assertSame(
            ['Second'],
            $this->repository
                ->all(search: 'eco')
                ->pluck('name')
                ->all()
        );
    }

    public function testItPaginatesAndFiltersPagesByStatus(): void
    {
        $active = $this->createPage('Active', 'active', 'active', null, 2);
        $inactive = $this->createPage('Inactive', 'inactive', 'inactive', null, 1, false);
        $deleted = $this->createPage('Deleted', 'deleted', 'deleted');
        $this->repository->delete($deleted);

        $activeResult = $this->repository->paginate(
            1,
            25,
            'position',
            'asc',
            null,
            ['status' => 'active']
        );
        $inactiveResult = $this->repository->paginate(
            1,
            25,
            filters: ['status' => 'inactive']
        );
        $deletedResult = $this->repository->paginate(
            1,
            25,
            filters: ['status' => 'deleted']
        );

        self::assertSame([$active->id], array_column($activeResult['data'], 'id'));
        self::assertSame([$inactive->id], array_column($inactiveResult['data'], 'id'));
        self::assertSame([$deleted->id], array_column($deletedResult['data'], 'id'));
        self::assertSame('deleted', $deletedResult['data'][0]['status']);
        self::assertSame(1, $deletedResult['pagination']['total']);
    }

    public function testItHandlesStatusPositionAndSoftDeleteLifecycle(): void
    {
        $first = $this->createPage('First', 'first', 'first', null, 1);
        $second = $this->createPage('Second', 'second', 'second', null, 2);

        $this->repository->setActive($first, false);
        $this->repository->updatePositions([
            ['id' => $first->id, 'position' => 4],
            ['id' => $second->id, 'position' => 3],
        ]);
        $this->repository->delete($second);

        self::assertFalse($first->fresh()->is_active);
        self::assertSame(4, $first->fresh()->position);
        self::assertSame([$second->id], $this->repository
            ->all(status: 'deleted')
            ->pluck('id')
            ->all());

        $deleted = $this->repository->findOrFail($second->id);
        $this->repository->restore($deleted);
        self::assertNull($deleted->fresh()->deleted_at);

        $this->repository->delete($deleted);
        $this->repository->forceDelete($deleted);
        self::assertNull($this->repository->find($second->id));
    }

    private function createPage(
        string $name,
        string $slug,
        string $fullSlug,
        ?int $parentId = null,
        int $position = 0,
        bool $active = true
    ): Page {
        return $this->repository->create([
            'name' => $name,
            'slug' => $slug,
            'full_slug' => $fullSlug,
            'parent_id' => $parentId,
            'position' => $position,
            'is_active' => $active,
        ]);
    }
}
