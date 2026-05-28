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
        Schema::create('listing_platform_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_platform_id')->constrained()->cascadeOnDelete();

            $table->string('external_id')->nullable(); // WordPress product ID
            $table->string('status')->default('draft'); // draft, published, failed
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['listing_id', 'listing_platform_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_platform_links');
    }
};
