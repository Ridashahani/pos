<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Muhammad Hamza', 'Ahmed Raza', 'Usman Ali', 'Ayesha Siddiqui', 'Fatima Noor', 'Hina Aslam']);

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'phone' => '03' . fake()->numerify('#########'),
            'address' => fake()->randomElement(['Saddar, Karachi', 'Hall Road, Lahore', 'Blue Area, Islamabad', 'University Road, Peshawar']),
            'city' => fake()->randomElement(['Karachi', 'Lahore', 'Islamabad', 'Peshawar']),
        ];
    }
}
