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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('status')->default('draft');   
            $table->date('purchase_date');
            $table->string('xero_id')->nullable();

            $table->text('notes')->nullable();
            $table->text('driver_notes')->nullable();
            $table->text('collection_notes')->nullable();
            $table->text('ideal_collection_date')->nullable();

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->boolean('deposit_paid')->default(false);
            $table->boolean('fully_paid')->default(false);

            $table->string('collected_by')->nullable();

            $table->string('collection_address_1')->nullable();
            $table->string('collection_address_2')->nullable();
            $table->string('collection_country', 4)->nullable();
            $table->string('collection_town_city')->nullable();
            $table->string('collection_postcode')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('purchases');
    }
};
