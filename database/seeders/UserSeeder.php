<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $manager = User::updateOrCreate(['email' => 'manager@gmail.com'], [
            'name' => 'Manager',
            'username' => 'manager',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
        ]);

         $cashier = User::updateOrCreate(['email' => 'cashier@gmail.com'], [
            'name' => 'Cashier',
            'username' => 'cashier',
            'email' => 'cashier@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $admin->syncRoles('Admin');
        $manager->syncRoles('Manager');
        $cashier->syncRoles('Cashier');
    }
}
