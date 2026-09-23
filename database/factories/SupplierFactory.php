<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Madina Mobile Traders', 'Al-Huda Accessories', 'Raza Mobile Wholesale', 'Bilal Telecom Supplies']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '03' . fake()->numerify('#########'),
            'address' => fake()->randomElement(['Hall Road, Lahore', 'Saddar Mobile Market, Karachi', 'Blue Area, Islamabad', 'Raja Bazaar, Rawalpindi']),
            'city' => fake()->randomElement(['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi']),
        ];
    }
}
