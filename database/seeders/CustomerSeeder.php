<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                 'name' => 'Muhammad Usman',
                 'email' => 'muhammad.usman@example.com',
                 'phone' => '03001234567',
                 'address' => 'Gulshan-e-Iqbal, Karachi, Pakistan'
            ],
            [
                 'name' => 'Ayesha Khan',
                 'email' => 'ayesha.khan@example.com',
                 'phone' => '03111234567',
                 'address' => 'Johar Town, Lahore, Pakistan'
            ],
            [
                 'name' => 'Hassan Raza',
                 'email' => 'hassan.raza@example.com',
                 'phone' => '03221234567',
                 'address' => 'Saddar, Rawalpindi, Pakistan'
            ],
            [
                 'name' => 'Bilal Ahmed',
                 'email' => 'bilal.ahmed@example.com',
                 'phone' => '03331234567',
                 'address' => 'University Road, Peshawar, Pakistan'
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
