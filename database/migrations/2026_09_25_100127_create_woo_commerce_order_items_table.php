<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('woocommerce_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('woocommerce_order_id')
                ->constrained('woocommerce_orders')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('woocommerce_line_item_id');

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('sku')->nullable();
            $table->string('title')->nullable();

            $table->integer('quantity')->default(1);

            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->json('raw')->nullable();

            $table->timestamps();

            $table->unique(
                ['woocommerce_order_id', 'woocommerce_line_item_id'],
                'wc_order_item_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_order_items');
    }
};
