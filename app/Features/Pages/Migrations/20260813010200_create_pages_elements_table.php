<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Schema\Blueprint;

final class CreatePagesElementsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            PagesTables::elements(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('element_type', 100);
                $table->unsignedInteger('position')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->index(
                    ['page_id', 'parent_id', 'position'],
                    'pages_elements_tree_position_index'
                );

                $table->foreign('page_id')
                    ->references('id')
                    ->on(PagesTables::pages())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(PagesTables::elements())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(PagesTables::elements());
    }
}
