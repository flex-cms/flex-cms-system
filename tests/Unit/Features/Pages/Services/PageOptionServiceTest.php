<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Services;

use Flex\Features\Pages\Exceptions\InvalidPageOptionException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageOption;
use Flex\Features\Pages\Repositories\EloquentPageOptionRepository;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

final class PageOptionServiceTest extends TestCase
{
    private Capsule $capsule;
    private PageOptionService $service;
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
            PagesTables::options(),
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('page_id');
                $table->string('option_key');
                $table->text('option_value')->nullable();
                $table->timestamps();
                $table->unique(['page_id', 'option_key']);
            }
        );

        $this->page = Page::query()->create([
            'name' => 'About',
            'slug' => 'about',
            'full_slug' => 'about',
            'position' => 0,
            'is_active' => true,
        ]);
        $this->service = new PageOptionService(
            new EloquentPageOptionRepository()
        );
    }

    protected function tearDown(): void
    {
        $this->capsule->getConnection()->disconnect();
        Model::unsetConnectionResolver();

        parent::tearDown();
    }

    public function testItPreservesValueTypesIncludingStringBooleans(): void
    {
        $this->service->saveMany($this->page, [
            'string_true' => 'true',
            'boolean_true' => true,
            'count' => 12,
            'metadata' => ['robots' => 'index'],
            'empty' => null,
        ]);

        self::assertSame([
            'boolean_true' => true,
            'count' => 12,
            'empty' => null,
            'metadata' => ['robots' => 'index'],
            'string_true' => 'true',
        ], $this->service->values($this->page));
    }

    public function testSaveManyPreservesOptionsThatWereNotSubmitted(): void
    {
        $this->service->saveMany($this->page, [
            'layout' => 'wide',
            'theme' => 'light',
        ]);
        $this->service->saveMany($this->page, [
            'layout' => 'boxed',
        ]);

        self::assertSame([
            'layout' => 'boxed',
            'theme' => 'light',
        ], $this->service->values($this->page));
    }

    public function testReplaceRemovesOptionsThatWereNotSubmitted(): void
    {
        $this->service->saveMany($this->page, [
            'layout' => 'wide',
            'theme' => 'light',
        ]);
        $this->service->replace($this->page, [
            'layout' => 'boxed',
        ]);

        self::assertSame(
            ['layout' => 'boxed'],
            $this->service->values($this->page)
        );
    }

    public function testReplaceWithEmptyArrayRemovesAllOptions(): void
    {
        $this->service->save($this->page, 'layout', 'wide');

        $this->service->replace($this->page, []);

        self::assertSame([], $this->service->values($this->page));
    }

    public function testItReadsDefaultAndRemovesOption(): void
    {
        self::assertSame(
            'fallback',
            $this->service->value($this->page, 'layout', 'fallback')
        );

        $this->service->save($this->page, 'layout', 'wide');

        self::assertSame('wide', $this->service->value($this->page, 'layout'));
        self::assertTrue($this->service->remove($this->page, 'layout'));
        self::assertFalse($this->service->remove($this->page, 'layout'));
    }

    public function testItRejectsUnknownOptionBeforeWriting(): void
    {
        $this->expectException(InvalidPageOptionException::class);
        $this->expectExceptionMessage('Page option [script] is not allowed.');

        $this->service->saveMany(
            $this->page,
            [
                'layout' => 'wide',
                'script' => 'unsafe',
            ],
            ['layout']
        );
    }

    public function testItRejectsInvalidOptionKey(): void
    {
        $this->expectException(InvalidPageOptionException::class);

        $this->service->save($this->page, 'invalid key', 'value');
    }

    public function testItRejectsValueThatCannotBeEncoded(): void
    {
        $this->expectException(InvalidPageOptionException::class);

        $this->service->save($this->page, 'invalid_number', NAN);
    }

    public function testRepositoryUpdatesExistingOptionInsteadOfDuplicatingIt(): void
    {
        $this->service->save($this->page, 'layout', 'wide');
        $this->service->save($this->page, 'layout', 'boxed');

        self::assertSame(1, PageOption::query()->count());
        self::assertSame('boxed', $this->service->value($this->page, 'layout'));
    }
}
