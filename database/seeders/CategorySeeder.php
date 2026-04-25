<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------
        // DOORS
        // -----------------------
        $door = Category::create([
            'name' => 'Door',
            'slug' => 'door',
        ]);

        $this->createChildren($door, [
            'Front Door',
            'Back Door',
            'Bifold Door',
            'Sliding Door',
            'French Door',
            'Garage Door',
            'Internal Door',
        ]);

        // -----------------------
        // WINDOWS
        // -----------------------
        $window = Category::create([
            'name' => 'Window',
            'slug' => 'window',
        ]);

        $this->createChildren($window, [
            'Casement Window',
            'Sash Window',
            'Bay Window',
            'Tilt & Turn Window',
            'Fixed Window',
        ]);

        // -----------------------
        // OTHER
        // -----------------------
        Category::create([
            'name' => 'Other',
            'slug' => 'other',
        ]);
    }

    protected function createChildren($parent, array $children)
    {
        foreach ($children as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_id' => $parent->id,
            ]);
        }
    }
}