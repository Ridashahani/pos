<?php

namespace Database\Seeders;

use App\Models\Variation;
use Illuminate\Database\Seeder;

class VariationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            [
                [
                    'name' => 'Color',
                    'types' => [
                        'Red',
                        'Blue',
                        'Black'
                    ]
                ],
                [
                    'name' => 'Size',
                    'types' => [
                        'X',
                        'XL',
                        'XXL',
                        'M',
                        'Small',
                        'Medium',
                        'Large'
                    ]
                ],
                [
                    'name' => 'Storage',
                    'types' => [
                        '32GB',
                        '64GB',
                        '128GB',
                        '256GB',
                        '512GB',
                        '1TB',
                    ],
                ],
                [
                    'name' => 'RAM',
                    'types' => [
                        '2GB',
                        '3GB',
                        '4GB',
                        '6GB',
                        '8GB',
                        '12GB',
                        '16GB',
                        '24GB',
                    ],
                ],
                [
                    'name' => 'Network',
                    'types' => [
                        '4G',
                        '5G',
                        'WiFi',
                        'WiFi + Cellular',
                    ],
                ],
                [
                    'name' => 'SIM',
                    'types' => [
                        'Single SIM',
                        'Dual SIM',
                        'eSIM',
                        'Dual SIM + eSIM',
                    ],
                ],
            ] as $variation
        ) {
            Variation::updateOrCreate(['name' => $variation['name']], $variation);
        }
    }
}
