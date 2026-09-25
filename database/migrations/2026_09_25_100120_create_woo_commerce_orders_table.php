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
        Schema::create('woocommerce_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('woocommerce_order_id')->unique();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->string('status')->nullable();

            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);

            $table->string('currency', 10)->default('GBP');

            $table->timestamp('ordered_at')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_orders');
    }
};
