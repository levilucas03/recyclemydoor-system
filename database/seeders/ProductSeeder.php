<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Product;


class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = Listing::all();

        foreach ($listings as $listing) {
            // Each listing gets 3 products
            Product::factory()->count(3)->create([
                'user_id' => $listing->user_id,
                'listing_id' => $listing->id,
            ]);
        }
    }
}
