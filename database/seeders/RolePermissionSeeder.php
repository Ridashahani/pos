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
        
        Permission::whereIn('name', [
            'access.pos', 'access.customers', 'access.suppliers', 'access.categories', 'access.products',
            'access.sales', 'access.stocks', 'access.roles', 'access.users',
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
