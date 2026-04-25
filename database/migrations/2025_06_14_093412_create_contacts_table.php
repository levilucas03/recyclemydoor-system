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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('type');
            $table->string('ebay_username')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 15)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->text('note')->nullable();
            $table->string('xero_id')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('country', 3)->nullable();
            $table->string('town_city')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('invoice_address_1')->nullable();
            $table->string('invoice_address_2')->nullable();
            $table->string('invoice_country', 3)->nullable();
            $table->string('invoice_town_city')->nullable();
            $table->string('invoice_postcode', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
