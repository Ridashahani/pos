<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Smartphones (Category 1)
            [
                'name' => 'Apple iPhone 15',
                'category_id' => 1,
                'stock' => 10,
                'buying_price' => 215000,
                'selling_price' => 239999,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'category_id' => 1,
                'stock' => 12,
                'buying_price' => 185000,
                'selling_price' => 209999,
            ],
            // Mobile Accessories (Category 2)
            [
                'name' => 'Universal Mobile Holder',
                'category_id' => 2,
                'stock' => 45,
                'buying_price' => 850,
                'selling_price' => 1299,
            ],
            [
                'name' => 'Mobile Camera Lens Kit',
                'category_id' => 2,
                'stock' => 25,
                'buying_price' => 1800,
                'selling_price' => 2499,
            ],
            // Chargers & Cables (Category 3)
            [
                'name' => 'Anker 20W Fast Charger',
                'category_id' => 3,
                'stock' => 50,
                'buying_price' => 2200,
                'selling_price' => 2999,
            ],
            [
                'name' => 'Type-C Fast Charging Cable',
                'category_id' => 3,
                'stock' => 100,
                'buying_price' => 450,
                'selling_price' => 799,
            ],
            // Mobile Covers & Cases (Category 4)
            [
                'name' => 'iPhone 15 Silicone Case',
                'category_id' => 4,
                'stock' => 60,
                'buying_price' => 650,
                'selling_price' => 1199,
            ],
            [
                'name' => 'Samsung Galaxy S24 Armor Case',
                'category_id' => 4,
                'stock' => 45,
                'buying_price' => 800,
                'selling_price' => 1499,
            ],
            // Screen Protectors (Category 5)
            [
                'name' => '9D Tempered Glass Protector',
                'category_id' => 5,
                'stock' => 120,
                'buying_price' => 180,
                'selling_price' => 399,
            ],
            // Power Banks (Category 6)
            [
                'name' => 'Anker 10000mAh Power Bank',
                'category_id' => 6,
                'stock' => 30,
                'buying_price' => 4500,
                'selling_price' => 5999,
            ],
            // Earbuds & Headphones (Category 7)
            [
                'name' => 'AirPods Pro 2',
                'category_id' => 7,
                'stock' => 18,
                'buying_price' => 52000,
                'selling_price' => 64999,
            ],
            // Smartwatches (Category 8)
            [
                'name' => 'Samsung Galaxy Watch 6',
                'category_id' => 8,
                'stock' => 14,
                'buying_price' => 42000,
                'selling_price' => 52999,
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'code' => IdGenerator::generate([
                    'table' => 'products',
                    'field' => 'code',
                    'length' => 10,
                    'prefix' => 'PRD-'
                ]),
                'category_id' => $product['category_id'],
                'stock' => $product['stock'],
                'buying_price' => $product['buying_price'],
                'selling_price' => $product['selling_price'],
                'buying_date' => now()->subDays(rand(1, 30)),
                'expire_date' => now()->addYear(),
                'image' => null,
            ]);
        }
    }
}
