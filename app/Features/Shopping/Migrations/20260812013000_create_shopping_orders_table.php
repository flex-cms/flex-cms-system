<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingOrdersTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_orders',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('number', 50)->unique();

                $table->unsignedBigInteger('customer_id')->nullable();

                $table->string('status', 40)->default('pending');
                $table->string('payment_status', 40)->default('pending');
                $table->string('payment_method', 80)->nullable();
                $table->string('shipping_method', 80)->nullable();

                $table->string('currency', 3)->default('BGN');

                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_total', 12, 2)->default(0);
                $table->decimal('shipping_total', 12, 2)->default(0);
                $table->decimal('tax_total', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);

                $table->string('customer_email', 190)->nullable();
                $table->string('customer_phone', 50)->nullable();

                // Snapshots: the order must preserve the original
                // addresses even if the customer later edits them.
                $table->json('billing_address')->nullable();
                $table->json('shipping_address')->nullable();

                $table->text('customer_note')->nullable();
                $table->text('admin_note')->nullable();

                $table->timestamp('placed_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();

                $table->timestamps();

                $table->index('customer_id');
                $table->index('status');
                $table->index('payment_status');
                $table->index('placed_at');

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
            'shopping_orders'
        );
    }
}
