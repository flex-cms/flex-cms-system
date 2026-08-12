<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class CreateAuthenticationRolePermissionTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            AuthenticationTables::rolePermission(),
            static function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');

                $table->primary(['role_id', 'permission_id']);

                $table->foreign('role_id')
                    ->references('id')
                    ->on(AuthenticationTables::roles())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('permission_id')
                    ->references('id')
                    ->on(AuthenticationTables::permissions())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(AuthenticationTables::rolePermission());
    }
}
