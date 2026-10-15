<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permission if it doesn't exist (outside transaction)
        // Using firstOrCreate ensures it only creates if it doesn't exist
        Permission::firstOrCreate(
            ['name' => 'product.menu'],
            ['group_name' => 'product']
        );
        
        // Create a role and assign permission (outside transaction)
        $role = Role::firstOrCreate(['name' => 'test-role']);
        if (!$role->hasPermissionTo('product.menu')) {
            $role->givePermissionTo('product.menu');
        }
    }

    protected function createAuthenticatedUser(): User
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'test-role')->first();
        $user->assignRole($role);
        return $user;
    }

    protected function createCategory(): Category
    {
        return Category::factory()->create();
    }

    public function test_product_can_be_created_with_manual_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Test Product',
            'code' => 'MANUAL-CODE-123',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
            'condition' => 'used',
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'code' => 'MANUAL-CODE-123',
            'condition' => 'used',
        ]);
    }

    public function test_product_sale_price_is_saved_and_listed_without_adding_gst(): void
    {
        $user = $this->createAuthenticatedUser();
        $role = Role::where('name', 'test-role')->firstOrFail();
        foreach (['access.products', 'create.products'] as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['group_name' => 'product']
            );
            $role->givePermissionTo($permission);
        }

        $branch = Branch::create([
            'name' => 'Product Test Branch',
            'address' => '123 Test Street',
            'status' => 'Active',
        ]);
        $user->branches()->attach($branch->id);
        $user->update(['active_branch_id' => $branch->id]);
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'GST Price Product',
            'code' => 'GST-PRICE-001',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 80,
            'selling_price' => 123.45,
            'gst_tax' => 18,
            'status' => 1,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'code' => 'GST-PRICE-001',
            'selling_price' => 123.45,
            'gst_tax' => 18,
        ]);

        $this->actingAs($user)->get('/products')
            ->assertOk()
            ->assertSee('123.45')
            ->assertDontSee('145.67');
    }

    public function test_product_code_is_auto_generated_when_not_provided(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Auto Code Product',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product = Product::where('name', 'Auto Code Product')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->code);
        $this->assertStringStartsWith('PC', $product->code);
    }

    public function test_product_creation_fails_with_duplicate_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create first product with a code
        Product::factory()->create([
            'code' => 'DUPLICATE-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Duplicate Code Product',
            'code' => 'DUPLICATE-CODE',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_product_creation_with_valid_code_succeeds(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Valid Code Product',
            'code' => 'VALID-CODE-001',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Valid Code Product',
            'code' => 'VALID-CODE-001',
        ]);
    }

    public function test_product_code_field_is_editable_in_create_form(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/products/create');

        $response->assertOk();
        $response->assertSee('Product Code', false);
        $response->assertSee('Barcode Scanner', false);
        $response->assertSee('id="code"', false);
        $response->assertSee('id="barcode_scanner"', false);
    }

    public function test_product_code_can_be_updated(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'OLD-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => $product->name,
            'code' => 'NEW-CODE-123',
            'category_id' => $category->id,
            'stock' => $product->stock,
            'buying_price' => $product->buying_price,
            'selling_price' => $product->selling_price,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('NEW-CODE-123', $product->code);
    }

    public function test_product_code_update_fails_with_duplicate(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create two products
        $product1 = Product::factory()->create([
            'code' => 'EXISTING-CODE',
            'category_id' => $category->id,
        ]);

        $product2 = Product::factory()->create([
            'code' => 'ORIGINAL-CODE',
            'category_id' => $category->id,
        ]);

        // Try to update product2 with product1's code
        $response = $this->actingAs($user)->put("/products/{$product2->id}", [
            'name' => $product2->name,
            'code' => 'EXISTING-CODE',
            'category_id' => $category->id,
            'stock' => $product2->stock,
            'buying_price' => $product2->buying_price,
            'selling_price' => $product2->selling_price,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_product_code_can_remain_unchanged_on_update(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'ORIGINAL-CODE',
            'category_id' => $category->id,
        ]);

        $originalCode = $product->code;

        $response = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'category_id' => $category->id,
            'stock' => $product->stock,
            'buying_price' => $product->buying_price,
            'selling_price' => $product->selling_price,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals($originalCode, $product->code);
        $this->assertEquals('Updated Product Name', $product->name);
    }

    public function test_product_code_field_is_editable_in_edit_form(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'TEST-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}/edit");

        $response->assertOk();
        $response->assertSee('Product Code', false);
        $response->assertSee('Barcode Scanner', false);
        $response->assertSee('value="TEST-CODE"', false);
        $response->assertSee('id="code"', false);
        $response->assertSee('id="barcode_scanner"', false);
    }

    public function test_code_max_length_validation(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $longCode = str_repeat('A', 51); // 51 characters

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Long Code Product',
            'code' => $longCode,
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_code_accepts_alphanumeric_characters(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Alphanumeric Code Product',
            'code' => 'ABC123-XYZ456',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Alphanumeric Code Product',
            'code' => 'ABC123-XYZ456',
        ]);
    }

    public function test_code_is_optional_in_create_request(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Optional Code Product',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product = Product::where('name', 'Optional Code Product')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->code); // Should be auto-generated
    }

    public function test_barcode_generation_uses_product_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'BARCODE-TEST-123',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee('BARCODE-TEST-123', false);
        $response->assertSee('Product Code', false);
        $response->assertSee('Product Barcode', false);
    }

    public function test_product_with_custom_code_displays_correct_barcode(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $customCode = 'CUSTOM-CODE-999';

        $product = Product::factory()->create([
            'code' => $customCode,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee($customCode, false);
        
        // Verify the product code is displayed in the readonly input
        $response->assertSee('value="' . $customCode . '"', false);
    }
}
