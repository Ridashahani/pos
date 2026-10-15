<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = collect(['access.roles', 'create.roles', 'edit.roles', 'delete.roles'])
            ->map(fn ($name) => Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => config('auth.defaults.guard')],
                ['group_name' => 'roles']
            ));
        $accessRole = Role::firstOrCreate([
            'name' => 'role-crud-test',
            'guard_name' => config('auth.defaults.guard'),
        ]);
        $accessRole->syncPermissions($permissions);

        $branch = Branch::create([
            'name' => 'Role CRUD Test Branch',
            'address' => 'Test Address',
            'status' => 'Active',
        ]);
        $this->user = User::factory()->create(['active_branch_id' => $branch->id]);
        $this->user->branches()->attach($branch->id);
        $this->user->assignRole($accessRole);
    }

    public function test_view_permission_does_not_allow_creating_customers(): void
    {
        $this->user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'access.customers', 'guard_name' => config('auth.defaults.guard')],
            ['group_name' => 'customer']
        ));

        $this->actingAs($this->user)
            ->post(route('customers.store'), [])
            ->assertForbidden();
    }

    public function test_create_permission_opens_customer_form_without_view_permission(): void
    {
        $this->user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'create.customers', 'guard_name' => config('auth.defaults.guard')],
            ['group_name' => 'customer']
        ));

        $this->actingAs($this->user)
            ->get(route('customers.create'))
            ->assertOk()
            ->assertViewIs('customers.create');
    }

    public function test_permission_list_search_filters_by_permission_key(): void
    {
        Permission::firstOrCreate(
            ['name' => 'access.customers', 'guard_name' => config('auth.defaults.guard')],
            ['group_name' => 'customer']
        );
        Permission::firstOrCreate(
            ['name' => 'create.customers', 'guard_name' => config('auth.defaults.guard')],
            ['group_name' => 'customer']
        );

        $this->actingAs($this->user)
            ->get(route('permission.index', ['search' => 'access.customers']))
            ->assertOk()
            ->assertSee('View')
            ->assertSee('access.customers')
            ->assertDontSee('create.customers');
    }

    public function test_custom_role_can_be_created_and_duplicate_names_are_rejected(): void
    {
        $this->actingAs($this->user)
            ->post(route('role.store'), ['name' => 'Inventory Auditor'])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'name' => 'Inventory Auditor',
            'guard_name' => config('auth.defaults.guard'),
        ]);

        $this->actingAs($this->user)
            ->from(route('role.create'))
            ->post(route('role.store'), ['name' => 'Inventory Auditor'])
            ->assertRedirect(route('role.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_role_can_be_renamed_and_deleted(): void
    {
        $role = Role::create(['name' => 'Temporary Supervisor']);

        $this->actingAs($this->user)
            ->put(route('role.update', $role->id), ['name' => 'Warehouse Supervisor'])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Warehouse Supervisor',
        ]);

        $this->actingAs($this->user)
            ->delete(route('role.destroy', $role->id))
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}