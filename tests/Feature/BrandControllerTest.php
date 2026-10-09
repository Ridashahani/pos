<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(
            ['name' => 'access.categories'],
            ['group_name' => 'category']
        );

        $role = Role::firstOrCreate(['name' => 'brand-test-role']);
        if (! $role->hasPermissionTo('access.categories')) {
            $role->givePermissionTo('access.categories');
        }
    }

    public function test_brand_creation_succeeds_and_duplicate_name_fails_validation(): void
    {
        $user = $this->createBrandManager();

        $this->actingAs($user)->post(route('brands.store'), ['name' => 'Test Brand'])
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);

        $this->actingAs($user)->post(route('brands.store'), ['name' => 'Test Brand'])
            ->assertSessionHasErrors('name');
    }

    public function test_renaming_brand_keeps_products_linked_to_its_id(): void
    {
        $user = $this->createBrandManager();
        $brand = Brand::create(['name' => 'Old Brand']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $this->actingAs($user)->put(route('brands.update', $brand), ['name' => 'New Brand'])
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'New Brand']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'brand_id' => $brand->id]);
        $this->assertSame('New Brand', $product->fresh()->brand->name);
    }

    public function test_brand_can_be_updated_without_changing_its_name(): void
    {
        $user = $this->createBrandManager();
        $brand = Brand::create(['name' => 'Unchanged Brand']);

        $this->actingAs($user)->put(route('brands.update', $brand), ['name' => 'Unchanged Brand'])
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'Unchanged Brand']);
    }

    public function test_brand_with_products_cannot_be_deleted_and_unused_brand_can_be_deleted(): void
    {
        $user = $this->createBrandManager();
        $usedBrand = Brand::create(['name' => 'Used Brand']);
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $usedBrand->id,
        ]);

        $this->actingAs($user)->delete(route('brands.destroy', $usedBrand))
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('brands', ['id' => $usedBrand->id]);

        $unusedBrand = Brand::create(['name' => 'Unused Brand']);
        $this->actingAs($user)->delete(route('brands.destroy', $unusedBrand))
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('brands', ['id' => $unusedBrand->id]);
    }

    private function createBrandManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'brand-test-role')->firstOrFail());

        return $user;
    }
}
