<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Source;


class SourceSeeder extends Seeder
{
    public function run(): void
    {

        Source::insert([
            ['name' => 'Website'],
            ['name' => 'eBay'],
            ['name' => 'Facebook'],
            ['name' => 'Gumtree'],
            ['name' => 'Email'],
            ['name' => 'Walk-in'],
            ['name' => 'Trade'],
        ]);
    }
}