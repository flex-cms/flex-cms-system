<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCouponUsagesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_coupon_usages',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('coupon_id');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('customer_id')->nullable();

                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->timestamp('used_at')->useCurrent();

                $table->unique([
                    'coupon_id',
                    'order_id',
                ]);

                $table->index([
                    'coupon_id',
                    'customer_id',
                ]);

                $table->foreign('coupon_id')
                    ->references('id')
                    ->on('shopping_coupons')
                    ->cascadeOnDelete();

                $table->foreign('order_id')
                    ->references('id')
                    ->on('shopping_orders')
                    ->cascadeOnDelete();

                $table->foreign('customer_id')
                    ->references('id')
                    ->on('shopping_customers')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_coupon_usages'
        );
    }
}
