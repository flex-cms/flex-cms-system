<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class CreateAuthenticationUserRoleTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            AuthenticationTables::userRole(),
            static function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');

                $table->primary(['user_id', 'role_id']);

                $table->foreign('user_id')
                    ->references('id')
                    ->on(AuthenticationTables::users())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('role_id')
                    ->references('id')
                    ->on(AuthenticationTables::roles())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(AuthenticationTables::userRole());
    }
}
