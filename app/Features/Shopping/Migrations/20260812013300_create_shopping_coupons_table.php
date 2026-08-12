<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCouponsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_coupons',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->string('code', 100)->unique();
                $table->string('type', 30)->default('fixed');
                $table->decimal('value', 12, 2)->default(0);

                $table->decimal('minimum_order_amount', 12, 2)->nullable();
                $table->decimal('maximum_discount_amount', 12, 2)->nullable();

                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->unsignedInteger('usage_limit_per_customer')->nullable();

                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();

                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->index([
                    'is_active',
                    'expires_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_coupons'
        );
    }
}
