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
            // Computers & Laptops (Category 1)
            [
                'name' => 'MacBook Air M2',
                'category_id' => 1,
                'stock' => 10,
                'buying_price' => 900,
                'selling_price' => 1100,
            ],
            [
                'name' => 'ASUS ROG Zephyrus G14',
                'category_id' => 1,
                'stock' => 5,
                'buying_price' => 1400,
                'selling_price' => 1700,
            ],
            // Smartphones & Tablets (Category 2)
            [
                'name' => 'iPhone 14 Pro Max',
                'category_id' => 2,
                'stock' => 15,
                'buying_price' => 1000,
                'selling_price' => 1200,
            ],
            [
                'name' => 'Samsung Galaxy Tab S9 Ultra',
                'category_id' => 2,
                'stock' => 8,
                'buying_price' => 1100,
                'selling_price' => 1300,
            ],
            // Computer Accessories (Category 3)
            [
                'name' => 'Logitech MX Master 3S',
                'category_id' => 3,
                'stock' => 50,
                'buying_price' => 80,
                'selling_price' => 100,
            ],
            [
                'name' => 'Keychron K2 Pro Mechanical Keyboard',
                'category_id' => 3,
                'stock' => 20,
                'buying_price' => 90,
                'selling_price' => 120,
            ],
            // Smartwatches (Category 4)
            [
                'name' => 'Apple Watch Series 9',
                'category_id' => 4,
                'stock' => 12,
                'buying_price' => 350,
                'selling_price' => 450,
            ],
            // Cameras & Audio (Category 5)
            [
                'name' => 'Sony Alpha a7 IV Body Only',
                'category_id' => 5,
                'stock' => 3,
                'buying_price' => 2200,
                'selling_price' => 2500,
            ],
            // Gaming Gear (Category 7)
            [
                'name' => 'SteelSeries Arctis Nova Pro',
                'category_id' => 7,
                'stock' => 10,
                'buying_price' => 300,
                'selling_price' => 350,
            ],
            [
                'name' => 'Google Pixel 9 Pro',
                'brand' => 'Google',
                'model' => 'Pixel 9 Pro',
                'imei' => '356789012345671',
                'category_id' => 2,
                'stock' => 12,
                'buying_price' => 850,
                'selling_price' => 999,
            ],
            [
                'name' => 'OnePlus 12',
                'brand' => 'OnePlus',
                'model' => 'CPH2573',
                'imei' => '356789012345672',
                'category_id' => 2,
                'stock' => 10,
                'buying_price' => 700,
                'selling_price' => 849,
            ],
            [
                'name' => 'Dell XPS 15',
                'brand' => 'Dell',
                'model' => 'XPS 15 9530',
                'category_id' => 1,
                'stock' => 6,
                'buying_price' => 1250,
                'selling_price' => 1500,
            ],
            [
                'name' => 'Lenovo ThinkPad X1 Carbon',
                'brand' => 'Lenovo',
                'model' => 'Gen 12',
                'category_id' => 1,
                'stock' => 7,
                'buying_price' => 1300,
                'selling_price' => 1550,
            ],
            [
                'name' => 'HP Spectre x360',
                'brand' => 'HP',
                'model' => '14-eu0000',
                'category_id' => 1,
                'stock' => 5,
                'buying_price' => 1100,
                'selling_price' => 1350,
            ],
            [
                'name' => 'iPad Pro 13-inch',
                'brand' => 'Apple',
                'model' => 'M4 Wi-Fi',
                'imei' => '356789012345673',
                'category_id' => 2,
                'stock' => 9,
                'buying_price' => 950,
                'selling_price' => 1150,
            ],
            [
                'name' => 'Xiaomi 14 Ultra',
                'brand' => 'Xiaomi',
                'model' => '24030PN60G',
                'imei' => '356789012345674',
                'category_id' => 2,
                'stock' => 14,
                'buying_price' => 780,
                'selling_price' => 920,
            ],
            [
                'name' => 'Anker USB-C Hub',
                'brand' => 'Anker',
                'model' => 'PowerExpand 8-in-1',
                'category_id' => 3,
                'stock' => 30,
                'buying_price' => 45,
                'selling_price' => 65,
            ],
            [
                'name' => 'Samsung T7 Portable SSD',
                'brand' => 'Samsung',
                'model' => 'MU-PC1T0T',
                'category_id' => 3,
                'stock' => 18,
                'buying_price' => 75,
                'selling_price' => 105,
            ],
            [
                'name' => 'Bose QuietComfort Ultra',
                'brand' => 'Bose',
                'model' => 'QC Ultra Headphones',
                'category_id' => 5,
                'stock' => 11,
                'buying_price' => 280,
                'selling_price' => 350,
            ],
            [
                'name' => 'Canon EOS R6 Mark II',
                'brand' => 'Canon',
                'model' => 'EOS R6 Mark II',
                'category_id' => 5,
                'stock' => 4,
                'buying_price' => 1900,
                'selling_price' => 2300,
            ],
            [
                'name' => 'TP-Link Archer AX73',
                'brand' => 'TP-Link',
                'model' => 'Archer AX73',
                'category_id' => 6,
                'stock' => 13,
                'buying_price' => 120,
                'selling_price' => 165,
            ],
            [
                'name' => 'Logitech Brio 4K Webcam',
                'brand' => 'Logitech',
                'model' => 'Brio 4K',
                'category_id' => 3,
                'stock' => 16,
                'buying_price' => 110,
                'selling_price' => 150,
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'brand' => 'Nintendo',
                'model' => 'HEG-001',
                'category_id' => 7,
                'stock' => 8,
                'buying_price' => 280,
                'selling_price' => 340,
            ],
            [
                'name' => 'Epson EcoTank Printer',
                'brand' => 'Epson',
                'model' => 'L3250',
                'category_id' => 8,
                'stock' => 6,
                'buying_price' => 180,
                'selling_price' => 240,
            ],
            [
                'name' => 'Microsoft Surface Laptop 6',
                'brand' => 'Microsoft',
                'model' => 'Surface Laptop 6',
                'category_id' => 1,
                'stock' => 5,
                'buying_price' => 1150,
                'selling_price' => 1400,
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'brand' => $product['brand'] ?? null,
                'model' => $product['model'] ?? null,
                'imei' => $product['imei'] ?? null,
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
