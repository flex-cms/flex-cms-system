<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class CreateAuthenticationPermissionsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            AuthenticationTables::permissions(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->string('module', 50);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(AuthenticationTables::permissions());
    }
}
