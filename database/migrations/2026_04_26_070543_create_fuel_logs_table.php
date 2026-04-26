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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->date('date');
            $table->decimal('litres', 8, 2);
            $table->decimal('cost', 8, 2);
            $table->decimal('price_per_litre', 6, 3)->nullable();

            $table->integer('mileage')->nullable(); // mileage at time of fill
            $table->string('location')->nullable(); // petrol station
            $table->text('notes')->nullable();  

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
