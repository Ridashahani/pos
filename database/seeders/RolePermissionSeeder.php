<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'access.pos' => 'pos',
            'access.customers' => 'customer',
            'access.suppliers' => 'supplier',
            'access.categories' => 'category',
            'access.products' => 'product',
            'access.sales' => 'sale',
            'access.stocks' => 'stock',
            'access.roles' => 'roles',
            'access.users' => 'user',
            'access.payments' => 'payment',
        ];

        Permission::whereIn('name', [
            'access.pos', 'access.customers', 'access.suppliers', 'access.categories', 'access.products',
            'access.sales', 'access.stocks', 'access.roles', 'access.users',
            'access.payments',
        ])->delete();

        foreach ($permissions as $name => $group) {
            Permission::firstOrCreate(['name' => $name], ['group_name' => $group]);
        }

        Role::whereNotIn('name', ['Admin', 'Manager', 'Cashier'])->get()->each->delete();

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier']);

        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'access.pos',
            'access.customers',
            'access.suppliers',
            'access.categories',
            'access.products',
            'access.sales',
            'access.stocks',
            'access.payments',
        ]);

        $cashier->syncPermissions([
            'access.pos',
            'access.customers',
            'access.sales',
            'access.payments',
        ]);
    }
}
