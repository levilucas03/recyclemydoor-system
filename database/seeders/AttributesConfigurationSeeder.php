<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\AttributeGroup;
use App\Models\Attribute;

class AttributesConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------
        // GROUP
        // -------------------------
        $group = AttributeGroup::firstOrCreate(
            ['slug' => 'configuration'],
            ['name' => 'Configuration']
        );

        // -------------------------
        // STRUCTURE
        // -------------------------
        $data = [
            'Inwards' => [
                'Inwards/Left',
                'Inwards/Right',
                'Inwards/Left and Right',
                'Inwards Folding',
                'Inwards Folding/Left',
                'Inwards Folding/Right',
            ],

            'Outwards' => [
                'Outwards/Left',
                'Outwards/Right',
                'Outwards/Left and Right',
                'Outwards Folding',
                'Outwards Folding/Left',
                'Outwards Folding/Right',
            ],

            'Slide' => [
                'Slide/Left',
                'Slide/Right',
                'Slide/Left and Right',
            ],

            'Vertical Slide' => [
                'Slide Up',
                'Slide Down',
                'Slide Up and Down',
                'Slide Up/Down and Tilt',
            ],

            'Tilt' => [
                'Tilt',
            ],

            'Fixed' => [
                'Fixed',
            ],
        ];

        // -------------------------
        // LOOP
        // -------------------------
        foreach ($data as $parentName => $children) {

            $parent = Attribute::firstOrCreate(
                [
                    'attribute_group_id' => $group->id,
                    'slug' => Str::slug($parentName),
                ],
                [
                    'name' => $parentName,
                    'parent_id' => null,
                ]
            );

            foreach ($children as $childName) {
                Attribute::firstOrCreate(
                    [
                        'attribute_group_id' => $group->id,
                        'slug' => Str::slug($childName),
                    ],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                    ]
                );
            }
        }
    }
}