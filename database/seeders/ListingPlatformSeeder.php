<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ListingPlatform;

class ListingPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ListingPlatform::updateOrCreate(
            ['slug' => 'wordpress'],
            [
                'name' => 'WordPress',
                'is_active' => false,
                'config' => [
                    'site_url' => '',
                    'consumer_key' => '',
                    'consumer_secret' => '',
                    'default_status' => 'draft',
                ],
            ]
        );
    }
}
