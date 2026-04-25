<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    public function run()
    {
        $types = ['Door', 'Window', 'Glass', 'Hardware'];

        foreach ($types as $type) {
            Type::create([
                'name' => $type,
                'slug' => \Str::slug($type),
            ]);
        }
    }
}
