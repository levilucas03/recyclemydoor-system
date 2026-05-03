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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('wc_id')->nullable();
            $table->string('ebay_id')->nullable();

            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->default('draft');   

            $table->string('xero_id')->nullable();

            $table->text('notes')->nullable();
            $table->text('planning_notes')->nullable();
            $table->string('source')->nullable();

            $table->date('predict_date')->nullable();
            $table->date('invoice_date')->nullable();

            $table->decimal('total_vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->boolean('deposit_paid')->default(false);
            $table->boolean('fully_paid')->default(false);

            // Delivery details
            $table->string('deliver_address_1')->nullable();
            $table->string('deliver_address_2')->nullable();
            $table->string('deliver_town_city')->nullable();
            $table->string('deliver_postcode')->nullable();

            $table->string('delivery_method')->nullable();

            $table->text('internal_note')->nullable();
            $table->text('customer_note')->nullable();

            $table->timestamps();
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale');
    }
};
