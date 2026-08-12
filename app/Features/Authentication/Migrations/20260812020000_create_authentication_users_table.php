<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class CreateAuthenticationUsersTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            AuthenticationTables::users(),
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('fullname', 100)->nullable();
                $table->string('email', 100)->unique();
                $table->string('password', 255);
                $table->string('remember_token', 100)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('options')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(AuthenticationTables::users());
    }
}
