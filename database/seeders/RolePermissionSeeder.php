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
        // group_name => plural name (permission naming ke liye)
        $modules = [
            'customer' => 'customers',
            'supplier' => 'suppliers',
            'category' => 'categories',
            'product'  => 'products',
            'sale'     => 'sales',
            'stock'    => 'stocks',
            'roles'    => 'roles',
            'user'     => 'users',
            'payment'  => 'payments',
        ];

        // POS ki sirf access permission
        Permission::firstOrCreate(['name' => 'access.pos'], ['group_name' => 'pos']);

        foreach ($modules as $group => $plural) {
            foreach (['access', 'create', 'edit', 'delete'] as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$action}.{$plural}"],
                    ['group_name' => $group]
                );
            }
        }

        Role::whereNotIn('name', ['Admin', 'Manager', 'Cashier'])->get()->each->delete();

        $admin   = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier']);

        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions(
            Permission::whereIn('group_name', ['pos', 'customer', 'supplier', 'category', 'product', 'sale', 'stock', 'payment'])->get()
        );

        $cashier->syncPermissions(
            Permission::whereIn('group_name', ['pos', 'customer', 'sale', 'payment'])->get()
        );
    }
}
