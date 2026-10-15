<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActiveBranchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_without_active_branch_is_blocked_and_redirected_to_select_branch()
    {
        $user = User::factory()->create([
            'active_branch_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('branch.select'));
        $this->assertEquals(url('/dashboard'), session('url.intended'));
    }

    public function test_user_can_view_select_branch_page_when_active_branch_is_null()
    {
        $user = User::factory()->create([
            'active_branch_id' => null,
        ]);

        $branch = Branch::create([
            'name' => 'Test Branch Alpha',
            'address' => '123 Main St',
            'status' => 'Active',
        ]);

        $user->branches()->attach($branch->id);

        $response = $this->actingAs($user)->get(route('branch.select'));

        $response->assertStatus(200);
        $response->assertSee('Select Active Branch');
        $response->assertSee('Test Branch Alpha');
    }

    public function test_setting_active_branch_saves_and_redirects_to_intended_url()
    {
        $user = User::factory()->create([
            'active_branch_id' => null,
        ]);

        $branch = Branch::create([
            'name' => 'Main City Branch',
            'address' => '456 Market St',
            'status' => 'Active',
        ]);

        $user->branches()->attach($branch->id);

        // Simulate intended url in session
        session(['url.intended' => url('/pos')]);

        $response = $this->actingAs($user)->post(route('branch.set-active'), [
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(url('/pos'));

        $user->refresh();
        $this->assertEquals($branch->id, $user->active_branch_id);
    }

    public function test_user_cannot_select_unassigned_branch()
    {
        $user = User::factory()->create([
            'active_branch_id' => null,
        ]);

        $branch = Branch::create([
            'name' => 'Unauthorized Branch',
            'address' => '789 Elm St',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('branch.set-active'), [
            'branch_id' => $branch->id,
        ]);

        $response->assertSessionHasErrors('branch_id');
        $this->assertNull($user->fresh()->active_branch_id);
    }

    public function test_user_with_active_branch_can_access_pages()
    {
        $branch = Branch::create([
            'name' => 'HQ Branch',
            'address' => '100 Headquarter Rd',
            'status' => 'Active',
        ]);

        $user = User::factory()->create([
            'active_branch_id' => $branch->id,
        ]);

        $user->branches()->attach($branch->id);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_when_branch_is_removed_active_branch_is_reset_to_null()
    {
        $branch1 = Branch::create(['name' => 'Branch 1', 'address' => 'Address 1', 'status' => 'Active']);
        $branch2 = Branch::create(['name' => 'Branch 2', 'address' => 'Address 2', 'status' => 'Active']);

        $user = User::factory()->create([
            'active_branch_id' => $branch1->id,
        ]);

        $user->branches()->attach([$branch1->id, $branch2->id]);

        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'access.users', 'group_name' => 'user']);
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo($permission);

        $admin = User::factory()->create([
            'active_branch_id' => $branch2->id,
        ]);
        $admin->branches()->attach($branch2->id);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->delete(route('user-branch.destroy', ['user' => $user->username, 'branch' => $branch1->id]));

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertNull($user->active_branch_id);
    }

    public function test_user_can_update_active_branch_from_profile()
    {
        $branch1 = Branch::create(['name' => 'Profile Branch 1', 'address' => 'Addr 1', 'status' => 'Active']);
        $branch2 = Branch::create(['name' => 'Profile Branch 2', 'address' => 'Addr 2', 'status' => 'Active']);

        $user = User::factory()->create([
            'active_branch_id' => $branch1->id,
        ]);
        $user->branches()->attach([$branch1->id, $branch2->id]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'username' => 'updateduser',
            'email' => 'updated@example.com',
            'active_branch_id' => $branch2->id,
        ]);

        $response->assertRedirect(route('profile'));
        $user->refresh();
        $this->assertEquals($branch2->id, $user->active_branch_id);
    }

    public function test_middleware_resets_active_branch_if_branch_no_longer_assigned()
    {
        $branch = Branch::create(['name' => 'Branch Removed Directly', 'address' => 'Direct Rd', 'status' => 'Active']);

        $user = User::factory()->create([
            'active_branch_id' => $branch->id,
        ]);

        // Branch is NOT attached to user in user_branch table
        // Accessing dashboard should detect that active_branch is invalid, reset it to null and redirect
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('branch.select'));
        $this->assertNull($user->fresh()->active_branch_id);
    }
    public function test_admin_bypasses_branch_selection_and_can_access_pages_directly()
    {
        $admin = User::factory()->create([
            'active_branch_id' => null,
        ]);
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $admin->assignRole($role);

        // Admin should NOT be redirected to branch selection even with null active_branch_id
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        // Admin visiting /select-branch should be redirected back to dashboard
        $response = $this->actingAs($admin)->get(route('branch.select'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_profile_does_not_require_branch()
    {
        $admin = User::factory()->create([
            'active_branch_id' => null,
        ]);
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $admin->assignRole($role);

        // Admin can update profile without active_branch_id
        $response = $this->actingAs($admin)->put(route('profile.update'), [
            'name' => 'Admin Updated',
            'username' => 'adminupdated',
            'email' => 'admin@example.com',
        ]);

        $response->assertRedirect(route('profile'));
    }
}
