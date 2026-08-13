<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Schema\Blueprint;

final class CreatePagesOptionsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            PagesTables::options(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('page_id');
                $table->string('option_key', 255);
                $table->longText('option_value')->nullable();
                $table->timestamps();

                $table->unique(
                    ['page_id', 'option_key'],
                    'pages_options_page_key_unique'
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
        $this->schema()->dropIfExists(PagesTables::options());
    }
}
