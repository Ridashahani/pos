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
            'Accessories',
            'Mobile',
           
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category);

            Category::updateOrCreate(['slug' => $slug], [
                'name' => $category,
                'slug' => $slug,
            ]);
        }
    }
}
