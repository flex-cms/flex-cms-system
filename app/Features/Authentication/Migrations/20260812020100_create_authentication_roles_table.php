<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class CreateAuthenticationRolesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            AuthenticationTables::roles(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->text('description')->nullable();
                $table->integer('priority')->default(0);
                $table->string('color', 7)->default('#6366f1');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('options')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('priority');
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(AuthenticationTables::roles());
    }
}
