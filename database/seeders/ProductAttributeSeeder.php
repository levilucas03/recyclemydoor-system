<?php

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\Type;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch type IDs
        $doorTypeId = Type::where('name', 'Door')->value('id');
        $windowTypeId = Type::where('name', 'Window')->value('id');

        // Attributes mostly for doors
        $doorAttributes = [
            'Handle Type',
            'Frame Material',
            'Locking Mechanism',
            'Opening Direction',
        ];

        // Attributes mostly for windows
        $windowAttributes = [
            'Glazing Type',
            'Opening Style',
            'Frame Color',
        ];

        foreach ($doorAttributes as $attribute) {
            ProductAttribute::create([
                'name' => $attribute,
                'slug' => Str::slug($attribute),
                'type_id' => $doorTypeId,
            ]);
        }

        foreach ($windowAttributes as $attribute) {
            ProductAttribute::create([
                'name' => $attribute,
                'slug' => Str::slug($attribute),
                'type_id' => $windowTypeId,
            ]);
        }
    }
}


