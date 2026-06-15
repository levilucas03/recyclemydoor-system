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
       Schema::create('ebay_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebay_account_id')->constrained()->cascadeOnDelete();

            $table->string('ebay_order_id')->unique();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->nullable();
            $table->string('buyer_username')->nullable();
            $table->string('buyer_email')->nullable();

            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('GBP');

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
        Schema::dropIfExists('ebay_orders');
    }
};
