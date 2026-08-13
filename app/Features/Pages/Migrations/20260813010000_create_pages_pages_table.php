<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Schema\Blueprint;

final class CreatePagesPagesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            PagesTables::pages(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->string('slug', 255);
                $table->string('full_slug', 512)->unique();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    ['parent_id', 'slug'],
                    'pages_pages_parent_slug_unique'
                );

                $table->index(
                    ['parent_id', 'position'],
                    'pages_pages_tree_position_index'
                );

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(PagesTables::pages())
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(PagesTables::pages());
    }
}
