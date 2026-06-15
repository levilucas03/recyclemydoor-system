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
        Schema::create('ebay_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebay_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('ebay_line_item_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('title')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebay_order_items');
    }
};
