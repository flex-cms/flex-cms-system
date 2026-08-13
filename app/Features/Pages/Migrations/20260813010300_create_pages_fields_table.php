<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Schema\Blueprint;

final class CreatePagesFieldsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            PagesTables::fields(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('page_id');
                $table->string('type', 50);
                $table->string('label', 255);
                $table->string('field_key', 100);
                $table->string('field_group', 100)->default('general');
                $table->unsignedInteger('position')->default(0);
                $table->text('hint')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->unique(
                    ['page_id', 'field_key'],
                    'pages_fields_page_key_unique'
                );
                $table->index(
                    ['page_id', 'field_group', 'position'],
                    'pages_fields_group_position_index'
                );
                $table->foreign('page_id')
                    ->references('id')
                    ->on(PagesTables::pages())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(PagesTables::fields());
    }
}
