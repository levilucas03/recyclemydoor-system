<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributeGroups = [
            ['name' => 'Brand', 'slug' => 'brand', 'type'],
            ['name' => 'Material', 'slug' => 'material'],
            ['name' => 'Condition', 'slug' => 'condition'],
            ['name' => 'Colour', 'slug' => 'colour'],
            ['name' => 'Glazing', 'slug' => 'Aluco'],
            ['name' => 'Traffic Door', 'slug' => 'traffic-door'],
            ['name' => 'Opening', 'slug' => 'opening'],
            ['name' => 'Configuration', 'slug' => 'configuration'],
            ['name' => 'Key', 'slug' => 'key'],
            ['name' => 'Glass', 'slug' => 'glass'],
            ['name' => 'Parts', 'slug' => 'parts'],
        ];

        foreach ($attributeGroups as $attributeGroup) {
            DB::table('attribute_groups')->insert([
                'name' => $attributeGroup['name'],
                'slug' => $attributeGroup['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
