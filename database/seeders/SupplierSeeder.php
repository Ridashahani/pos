<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                 'name' => 'Usman Mobile Traders',
                 'email' => 'sales@usmanmobile.pk',
                 'phone' => '03009876543',
                 'address' => 'Hall Road, Lahore, Pakistan'
            ],
            [
                 'name' => 'Al-Madina Mobile Accessories',
                 'email' => 'orders@almadinamobile.pk',
                 'phone' => '03119876543',
                 'address' => 'Saddar Mobile Market, Karachi, Pakistan'
            ],
            [
                 'name' => 'Hassan Electronics Wholesale',
                 'email' => 'info@hassanelectronics.pk',
                 'phone' => '03219876543',
                 'address' => 'Blue Area Mobile Market, Islamabad, Pakistan'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
