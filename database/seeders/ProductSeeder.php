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
                'image' => 'iphone15.jpg',
                
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'category_id' => 1,
                'stock' => 12,
                'buying_price' => 185000,
                'selling_price' => 209999,
                'image' => 'Samsung S24.jpg',
            ],
            // Mobile Accessories (Category 2)
            [
                'name' => 'Universal Mobile Holder',
                'category_id' => 2,
                'stock' => 45,
                'buying_price' => 850,
                'selling_price' => 1299,
                'image' => 'Mobile Holder.jpg',
            ],
            [
                'name' => 'Mobile Camera Lens Kit',
                'category_id' => 2,
                'stock' => 25,
                'buying_price' => 1800,
                'selling_price' => 2499,
                'image' => 'mobile-lens-kit.jpg',
            ],
            // Chargers & Cables (Category 3)
            [
                'name' => 'Anker 20W Fast Charger',
                'category_id' => 3,
                'stock' => 50,
                'buying_price' => 2200,
                'selling_price' => 2999,
                'image' => 'Anker-Charger.jpg',
            ],
            [
                'name' => 'Type-C Fast Charging Cable',
                'category_id' => 3,
                'stock' => 100,
                'buying_price' => 450,
                'selling_price' => 799,
                'image' => 'Type-C-Charging-Cable.jpg',
            ],
            // Mobile Covers & Cases (Category 4)
            [
                'name' => 'iPhone 15 Silicone Case',
                'category_id' => 4,
                'stock' => 60,
                'buying_price' => 650,
                'selling_price' => 1199,
                'image' => 'iPhone 15-Case.jpg',
            ],
            [
                'name' => 'Samsung Galaxy S24 Armor Case',
                'category_id' => 4,
                'stock' => 45,
                'buying_price' => 800,
                'selling_price' => 1499,
                'image' => 'Samsung S24.jpg',
            ],
            // Screen Protectors (Category 5)
            [
                'name' => '9D Tempered Glass Protector',
                'category_id' => 5,
                'stock' => 120,
                'buying_price' => 180,
                'selling_price' => 399,
                'image' => '9D-Protector.jpg',
            ],
            // Power Banks (Category 6)
            [
                'name' => 'Anker 10000mAh Power Bank',
                'category_id' => 6,
                'stock' => 30,
                'buying_price' => 4500,
                'selling_price' => 5999,
                'image' => 'Anker Power Bank.jpg',
            ],
            // Earbuds & Headphones (Category 7)
            [
                'name' => 'AirPods Pro 2',
                'category_id' => 7,
                'stock' => 18,
                'buying_price' => 52000,
                'selling_price' => 64999,
                'image' => 'AirPods Pro 2.jpg',
            ],
            // Smartwatches (Category 8)
            [
                'name' => 'Samsung Galaxy Watch 6',
                'category_id' => 8,
                'stock' => 14,
                'buying_price' => 42000,
                'selling_price' => 52999,
                'image' => 'Samsung Galaxy Watch.jpg',
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
                'image' => 'Gogle Pixel 6.jpg',
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
                'image' => 'OnePlus 12.jpg',
            ],
            [
                'name' => 'Vivo X200 Ultra',
                'brand' => 'Dell',
                'model' => 'XPS 15 9530',
                'category_id' => 1,
                'stock' => 6,
                'buying_price' => 1250,
                'selling_price' => 1500,
                'image' => 'Vivo X200 Ultra.jpg',
            ],
            // [
            //     'name' => 'Lenovo ThinkPad X1 Carbon',
            //     'brand' => 'Lenovo',
            //     'model' => 'Gen 12',
            //     'category_id' => 1,
            //     'stock' => 7,
            //     'buying_price' => 1300,
            //     'selling_price' => 1550,
            //     'image' => 'Dell XPS 15.jpg',
            // ],
            // [
            //     'name' => 'HP Spectre x360',
            //     'brand' => 'HP',
            //     'model' => '14-eu0000',
            //     'category_id' => 1,
            //     'stock' => 5,
            //     'buying_price' => 1100,
            //     'selling_price' => 1350,
            //     'image' => 'Hp- x360.jpg',
            // ],
            [
                'name' => 'iPad Pro 13-inch',
                'brand' => 'Apple',
                'model' => 'M4 Wi-Fi',
                'imei' => '356789012345673',
                'category_id' => 2,
                'stock' => 9,
                'buying_price' => 950,
                'selling_price' => 1150,
                'image' => 'iPad Pro.jpg',
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
                'image' => 'Xiaomi.jpg',
            ],
            [
                'name' => 'Anker USB-C Hub',
                'brand' => 'Anker',
                'model' => 'PowerExpand 8-in-1',
                'category_id' => 3,
                'stock' => 30,
                'buying_price' => 45,
                'selling_price' => 65,
                'image' => 'USB-C Hub.jpg',
            ],
            [
                'name' => 'Samsung T7 Portable SSD',
                'brand' => 'Samsung',
                'model' => 'MU-PC1T0T',
                'category_id' => 3,
                'stock' => 18,
                'buying_price' => 75,
                'selling_price' => 105,
                'image' => 'Samsung T7 Portable SSD.jpg',
            ],
            [
                'name' => 'Bose QuietComfort Ultra',
                'brand' => 'Bose',
                'model' => 'QC Ultra Headphones',
                'category_id' => 5,
                'stock' => 11,
                'buying_price' => 280,
                'selling_price' => 350,
                'image' => 'Bose QuietComfort Ultra.jpg',
            ],
            // [
            //     'name' => 'Canon EOS R6 Mark II',
            //     'brand' => 'Canon',
            //     'model' => 'EOS R6 Mark II',
            //     'category_id' => 5,
            //     'stock' => 4,
            //     'buying_price' => 1900,
            //     'selling_price' => 2300,
            //     'image' => 'Canon EOS R6 Mark II.jpg',
            // ],
            [
                'name' => 'TP-Link Archer AX73',
                'brand' => 'TP-Link',
                'model' => 'Archer AX73',
                'category_id' => 6,
                'stock' => 13,
                'buying_price' => 120,
                'selling_price' => 165,
                'image' => 'TP-Link.jpg',
            ],
            // [
            //     'name' => 'Logitech Brio 4K Webcam',
            //     'brand' => 'Logitech',
            //     'model' => 'Brio 4K',
            //     'category_id' => 3,
            //     'stock' => 16,
            //     'buying_price' => 110,
            //     'selling_price' => 150,
            //     'image' => 'Logitech Brio.jpg',
            // ],
            [
                'name' => 'Nintendo Switch OLED',
                'brand' => 'Nintendo',
                'model' => 'HEG-001',
                'category_id' => 7,
                'stock' => 8,
                'buying_price' => 280,
                'selling_price' => 340,
                'image' => 'Nintendo Switch.jpg',
            ],
            // [
            //     'name' => 'Epson EcoTank Printer',
            //     'brand' => 'Epson',
            //     'model' => 'L3250',
            //     'category_id' => 8,
            //     'stock' => 6,
            //     'buying_price' => 180,
            //     'selling_price' => 240,
            //     'image' => 'EpsonPrinter.jpg',
            // ],
            // [
            //     'name' => 'Microsoft Surface Laptop 6',
            //     'brand' => 'Microsoft',
            //     'model' => 'Surface Laptop 6',
            //     'category_id' => 1,
            //     'stock' => 5,
            //     'buying_price' => 1150,
            //     'selling_price' => 1400,
            //     'image' => 'MicrosoftLaptop 6.jpg',
            // ],
        ];

        foreach ($products as $product) {
            $slug = Str::slug($product['name']);
            $productData = [
                'name' => $product['name'],
                'brand' => $product['brand'] ?? null,
                'model' => $product['model'] ?? null,
                'imei' => $product['imei'] ?? null,
                'category_id' => $product['category_id'],
                'stock' => $product['stock'],
                'buying_price' => $product['buying_price'],
                'selling_price' => $product['selling_price'],
                'buying_date' => now()->subDays(rand(1, 30)),
                'expire_date' => now()->addYear(),
                'image' => $product['image'],
            ];

            $existingProduct = Product::where('slug', $slug)->first();

            if ($existingProduct) {
                $existingProduct->update($productData);
            } else {
                Product::create(array_merge($productData, [
                    'slug' => $slug,
                    'code' => IdGenerator::generate([
                        'table' => 'products',
                        'field' => 'code',
                        'length' => 10,
                        'prefix' => 'PRD-'
                    ]),
                ]));
            }
        }
    }
}
