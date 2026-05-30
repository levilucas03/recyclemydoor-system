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
        Schema::table('attribute_groups', function (Blueprint $table) {
            // WordPress term ID
            $table->unsignedBigInteger('wordpress_term_id')
                ->nullable()
                ->after('slug');

            // WordPress term slug
            $table->string('wordpress_slug')
                ->nullable()
                ->after('wordpress_term_id');

            // Taxonomy type
            // Examples:
            // product_cat
            // pa_material
            // pa_colour
            $table->string('wordpress_taxonomy')
                ->nullable()
                ->after('wordpress_slug');

            // WooCommerce global attribute ID
            $table->unsignedBigInteger('wordpress_attribute_id')
                ->nullable()
                ->after('wordpress_taxonomy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_groups', function (Blueprint $table) {
             $table->dropColumn([
                'wordpress_term_id',
                'wordpress_slug',
                'wordpress_taxonomy',
                'wordpress_attribute_id',
            ]);
        });
    }
};
