<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // ListingSeeder::class,
            // ProductSeeder::class,
            AttributeGroupsSeeder::class,
            AttributeBrandSeeder::class,
            AttributeColourSeeder::class,
            AttributeConditionSeeder::class,
            AttributeConfigurationSeeder::class,
            AttributeMaterialSeeder::class,
            AttributeOpeningSeeder::class,
            // ProductAttributeSeeder::class,
            // TypeSeeder::class,
            // BrandSeeder::class,
            PurchaseDemoSeeder::class,
            CategorySeeder::class,
    
        ]);
    }
}
