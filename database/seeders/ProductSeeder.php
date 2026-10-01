<?php

namespace Database\Seeders;

use App\Models\Category;
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
        // TRIM isliye ke agar naam ke shuru/aakhir mein space ho to bhi mil jaye
        $accessories = Category::whereRaw('TRIM(name) = ?', ['Accessories'])->value('id');
        $mobile      = Category::whereRaw('TRIM(name) = ?', ['Mobile'])->value('id');

        if (!$accessories || !$mobile) {
            $this->command?->error('Accessories ya Mobile category nahi mili. Pehle CategorySeeder chalao.');
            return;
        }

        $products = [
            // ---------- Mobile ----------
            [
                'name' => 'Apple iPhone 15',
                'model' => 'iPhone 15',
                'category_id' => $mobile,
                'stock' => 10,
                'cost_price' => 215000,
                'selling_price' => 239999,
                'image' => 'iphone15.jpg',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'model' => 'S24',
                'category_id' => $mobile,
                'stock' => 12,
                'cost_price' => 185000,
                'selling_price' => 209999,
                'image' => 'Samsung S24.jpg',
            ],
            [
                'name' => 'Google Pixel 9 Pro',
                'model' => 'Pixel 9 Pro',
                'imei' => '356789012345671',
                'category_id' => $mobile,
                'stock' => 12,
                'cost_price' => 850,
                'selling_price' => 999,
                'image' => 'Gogle Pixel 6.jpg',
            ],
            [
                'name' => 'OnePlus 12',
                'model' => 'CPH2573',
                'imei' => '356789012345672',
                'category_id' => $mobile,
                'stock' => 10,
                'cost_price' => 700,
                'selling_price' => 849,
                'image' => 'OnePlus 12.jpg',
            ],
            [
                'name' => 'Vivo X200 Ultra',
                'model' => 'X200 Ultra',
                'category_id' => $mobile,
                'stock' => 6,
                'cost_price' => 1250,
                'selling_price' => 1500,
                'image' => 'Vivo X200 Ultra.jpg',
            ],
            [
                'name' => 'iPad Pro 13-inch',
                'model' => 'M4 Wi-Fi',
                'imei' => '356789012345673',
                'category_id' => $mobile,
                'stock' => 9,
                'cost_price' => 950,
                'selling_price' => 1150,
                'image' => 'iPad Pro.jpg',
            ],
            [
                'name' => 'Xiaomi 14 Ultra',
                'model' => '24030PN60G',
                'imei' => '356789012345674',
                'category_id' => $mobile,
                'stock' => 14,
                'cost_price' => 780,
                'selling_price' => 920,
                'image' => 'Xiaomi.jpg',
            ],

            // ---------- Accessories ----------
            [
                'name' => 'Universal Mobile Holder',
                'category_id' => $accessories,
                'stock' => 45,
                'cost_price' => 850,
                'selling_price' => 1299,
                'image' => 'Mobile Holder.jpg',
            ],
            [
                'name' => 'Mobile Camera Lens Kit',
                'category_id' => $accessories,
                'stock' => 25,
                'cost_price' => 1800,
                'selling_price' => 2499,
                'image' => 'mobile-lens-kit.jpg',
            ],
            [
                'name' => 'Samsung Galaxy Watch 6',
                'category_id' => $accessories,
                'stock' => 14,
                'cost_price' => 42000,
                'selling_price' => 52999,
                'image' => 'Samsung Galaxy Watch.jpg',
            ],
            [
                'name' => 'Anker USB-C Hub',
                'model' => 'PowerExpand 8-in-1',
                'category_id' => $accessories,
                'stock' => 30,
                'cost_price' => 45,
                'selling_price' => 65,
                'image' => 'USB-C Hub.jpg',
            ],
            [
                'name' => 'Samsung T7 Portable SSD',
                'model' => 'MU-PC1T0T',
                'category_id' => $accessories,
                'stock' => 18,
                'cost_price' => 75,
                'selling_price' => 105,
                'image' => 'Samsung T7 Portable SSD.jpg',
            ],
            [
                'name' => 'Bose QuietComfort Ultra',
                'model' => 'QC Ultra Headphones',
                'category_id' => $accessories,
                'stock' => 11,
                'cost_price' => 280,
                'selling_price' => 350,
                'image' => 'Bose QuietComfort Ultra.jpg',
            ],
            [
                'name' => 'TP-Link Archer AX73',
                'model' => 'Archer AX73',
                'category_id' => $accessories,
                'stock' => 13,
                'cost_price' => 120,
                'selling_price' => 165,
                'image' => 'TP-Link.jpg',
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'model' => 'HEG-001',
                'category_id' => $accessories,
                'stock' => 8,
                'cost_price' => 280,
                'selling_price' => 340,
                'image' => 'Nintendo Switch.jpg',
            ],
        ];

        foreach ($products as $product) {
            $slug = Str::slug($product['name']);

            $productData = [
                'name' => $product['name'],
                'model' => $product['model'] ?? null,
                'imei' => $product['imei'] ?? null,
                'category_id' => $product['category_id'],
                'stock' => $product['stock'],
                'cost_price' => $product['cost_price'],
                'selling_price' => $product['selling_price'],
                'order_tax' => 17,
                'status' => 1,
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
                        'prefix' => 'PRD-',
                    ]),
                ]));
            }
        }
    }
}