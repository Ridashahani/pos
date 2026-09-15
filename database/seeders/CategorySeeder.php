<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Smartphones',
            'Mobile Accessories',
            'Chargers & Cables',
            'Mobile Covers & Cases',
            'Screen Protectors',
            'Power Banks',
            'Earbuds & Headphones',
            'Smartwatches'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
        }
    }
}
