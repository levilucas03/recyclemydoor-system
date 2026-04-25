<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttributesColourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $atribute = AttributeGroup::where('slug', 'colour')->firstOrFail();

        $names = [
            'White',
            'Grey',
            'Black',
            'Brown',
            'Blue',
            'Cream',
            'Green',
            'Red',
            'Silver',
            'Varnished',
        ];

        foreach ($names as $name) {
            Attribute::firstOrCreate([
                'attribute_group_id' => $atribute->id,
                'slug' => Str::slug($name),
            ], [
                'name' => $name,
                'attribute_group_id' => $atribute->id,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
