<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttributesBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandType = AttributeGroup::where('slug', 'brand')->firstOrFail();

        $brands = [
            'Unbranded',
            'Anglian',
            'Aliplast',
            'ALUK',
            'Aluco',
            'Alutech',
            'Atlas',
            'Bereco',
            'Chase',
            'Cortizo',
            'Deceuninck',
            'Door Stop',
            'Dutchman',
            'Duraflex',
            'Durafold',
            'Empress',
            'Eurocell',
            'Eurospec',
            'Evolve',
            'Everest',
            'Eternal',
            'FSD (Fold N Slide)',
            'Jeldwen',
            'Klein',
            'Kloeber',
            'Kommerling',
            'Liniar',
            'Maco',
            'Monarch',
            'Mumford & Wood',
            'Newbury',
            'Origin',
            'Phoenix',
            'Profine',
            'Profile',
            'Residence Collection',
            'Residence Company R9',
            'Rationel',
            'Rockdoor',
            'Rehau',
            'Reynaers',
            'Sabre',
            'Schuco',
            'Selecta',
            'Sheerframe',
            'Smart',
            'Sobinco',
            'Solidor',
            'Sowater Windows',
            'Spartan',
            'Sunflex',
            'Synseal',
            'Swift',
            'Technoform',
            'Timber Windows',
            'Ultimate',
            'Victorian Sliders',
            'Virtuoso',
            'Vufold',
            'WarmCore',
            'Yale',
            'Veka',
        ];

        foreach ($brands as $brand) {
            Attribute::firstOrCreate([
                'attribute_group_id' => $brandType->id,
                'slug' => Str::slug($brand),
            ], [
                'name' => $brand,
                'attribute_group_id' => $brandType->id,
                'slug' => Str::slug($brand),
            ]);
        }
    }
}
