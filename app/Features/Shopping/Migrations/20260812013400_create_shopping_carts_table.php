<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCartsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_carts',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('session_token', 190)->nullable()->unique();

                $table->string('currency', 3)->default('BGN');
                $table->unsignedBigInteger('coupon_id')->nullable();

                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index('customer_id');
                $table->index('expires_at');

                $table->foreign('customer_id')
                    ->references('id')
                    ->on('shopping_customers')
                    ->cascadeOnDelete();

                $table->foreign('coupon_id')
                    ->references('id')
                    ->on('shopping_coupons')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_carts'
        );
    }
}
