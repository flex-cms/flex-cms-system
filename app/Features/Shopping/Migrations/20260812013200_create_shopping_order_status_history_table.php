<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingOrderStatusHistoryTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_order_status_history',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id');

                $table->string('status', 40);
                $table->text('note')->nullable();
                $table->string('changed_by', 64)->nullable();

                $table->timestamp('created_at')->useCurrent();

                $table->index([
                    'order_id',
                    'created_at',
                ]);

                $table->foreign('order_id')
                    ->references('id')
                    ->on('shopping_orders')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_order_status_history'
        );
    }
}
