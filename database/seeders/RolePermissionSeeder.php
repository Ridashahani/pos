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
            'access.stock' => 'stock',
            'access.roles' => 'roles',
            'access.users' => 'user',
        ];

        Permission::whereIn('name', [
            'pos.menu', 'customer.menu', 'supplier.menu', 'category.menu', 'product.menu',
            'sale.menu', 'stock.menu', 'roles.menu', 'user.menu',
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
            'access.pos',
            'access.customers',
            'access.suppliers',
            'access.categories',
            'access.products',
            'access.sales',
        ]);
    }
}
