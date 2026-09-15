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
            'pos.menu' => 'pos',
            'customer.menu' => 'customer',
            'supplier.menu' => 'supplier',
            'category.menu' => 'category',
            'product.menu' => 'product',
            'sale.menu' => 'sale',
            'stock.menu' => 'stock',
            'roles.menu' => 'roles',
            'user.menu' => 'user',
        ];

        Permission::whereIn('name', [
            'employee.menu',
            'salary.menu',
            'attendance.menu',
            'orders.menu',
            'database.menu',
        ])->delete();

        foreach ($permissions as $name => $group) {
            Permission::firstOrCreate(['name' => $name], ['group_name' => $group]);
        }

        Role::whereNotIn('name', ['Admin', 'Staff'])->get()->each->delete();

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        $admin->syncPermissions(Permission::all());
        $staff->syncPermissions([
            'pos.menu',
            'customer.menu',
            'supplier.menu',
            'category.menu',
            'product.menu',
            'sale.menu',
        ]);
    }
}
