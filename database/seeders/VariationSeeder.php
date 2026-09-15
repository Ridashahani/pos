<?php

namespace Database\Seeders;

use App\Models\Variation;
use Illuminate\Database\Seeder;

class VariationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Color', 'types' => ['Red', 'Blue', 'Black']],
            ['name' => 'Colours', 'types' => ['Red', 'Green', 'Blue', 'Black', 'Yellow']],
            ['name' => 'Size', 'types' => ['X', 'XL', 'XXL', 'M', 'Small', 'Medium', 'Large']],
        ] as $variation) {
            Variation::updateOrCreate(['name' => $variation['name']], $variation);
        }
    }
}