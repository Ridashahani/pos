<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permission if it doesn't exist (outside transaction)
        // Using firstOrCreate ensures it only creates if it doesn't exist
        Permission::firstOrCreate(
            ['name' => 'pos.menu'],
            ['group_name' => 'pos']
        );
        
        // Create a role and assign permission (outside transaction)
        $role = Role::firstOrCreate(['name' => 'test-role']);
        if (!$role->hasPermissionTo('pos.menu')) {
            $role->givePermissionTo('pos.menu');
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

    protected function createProductWithCode(string $name, string $code, ?Category $category = null): Product
    {
        if (!$category) {
            $category = $this->createCategory();
        }

        return Product::factory()->create([
            'name' => $name,
            'code' => $code,
            'category_id' => $category->id,
            'expire_date' => Carbon::now()->addYear(), // Ensure product is not expired
        ]);
    }

    public function test_pos_page_requires_authentication(): void
    {
        $response = $this->get('/pos');

        // POS requires authentication and permission
        // Unauthenticated users get 403 (Forbidden) or redirect to login
        $this->assertTrue(
            $response->status() === 403 || 
            $response->status() === 302 ||
            $response->isRedirect('/login')
        );
    }

    public function test_pos_page_is_displayed_for_authenticated_users(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk();
        $response->assertViewIs('pos.index');
    }

    public function test_pos_branch_selector_contains_the_users_active_branches(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $branch = Branch::create(['name' => 'Assigned POS Branch', 'address' => 'Test address']);
        $user->branches()->attach($branch->id);

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk()
            ->assertSee('id="branch_id" name="branch_id"', false)
            ->assertSee('value="' . $branch->id . '"', false)
            ->assertSee('Assigned POS Branch', false);
    }

    public function test_sale_is_created_with_the_selected_assigned_branch(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $branch = Branch::create(['name' => 'Sale Test Branch', 'address' => 'Test address']);
        $user->branches()->attach($branch->id);
        $customer = Customer::factory()->create();
        $product = $this->createProductWithCode('Branch Sale Product', 'BRANCH-SALE-001');
        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100,
            'options' => [
                'original_price' => 100,
                'tax_rate' => 0,
                'discount' => 0,
                'currency' => 'PKR',
            ],
        ]);

        $response = $this->actingAs($user)->postJson('/pos/sale', [
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'payment_type' => 'Cash',
            'pay_amount' => 100,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('sales', ['branch_id' => $branch->id]);
    }

    public function test_pos_product_results_keep_a_stable_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $products = collect([
            $this->createProductWithCode('Stable Product A', 'STABLE-001'),
            $this->createProductWithCode('Stable Product B', 'STABLE-002'),
            $this->createProductWithCode('Stable Product C', 'STABLE-003'),
        ]);
        $products->each->update(['stock' => 5, 'selling_price' => 100]);

        $response = $this->actingAs($user)->getJson('/pos?search=Stable');

        $response->assertOk();
        preg_match_all('/name="id" value="(\d+)"/', $response->json('html'), $matches);
        $this->assertSame($products->pluck('id')->all(), array_map('intval', $matches[1]));
    }

    public function test_pos_search_by_product_name(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create products with different names
        $product1 = $this->createProductWithCode('Laptop Computer', 'LAPTOP-001', $category);
        $product2 = $this->createProductWithCode('Desktop Computer', 'DESKTOP-001', $category);
        $product3 = $this->createProductWithCode('Mouse Pad', 'MOUSE-001', $category);

        // Search by name
        $response = $this->actingAs($user)->get('/pos?search=Laptop');

        $response->assertOk();
        $response->assertSee('Laptop Computer', false);
        $response->assertDontSee('Desktop Computer', false);
        $response->assertDontSee('Mouse Pad', false);
    }

    public function test_pos_search_by_product_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create products with different codes
        $product1 = $this->createProductWithCode('Product One', 'BC-12345', $category);
        $product2 = $this->createProductWithCode('Product Two', 'BC-67890', $category);
        $product3 = $this->createProductWithCode('Product Three', 'BC-11111', $category);

        // Search by code
        $response = $this->actingAs($user)->get('/pos?search=BC-12345');

        $response->assertOk();
        $response->assertSee('Product One', false);
        $response->assertDontSee('Product Two', false);
        $response->assertDontSee('Product Three', false);
    }

    public function test_pos_search_finds_product_by_partial_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create product with specific code
        $product = $this->createProductWithCode('Scannable Product', 'BC-12345-XYZ', $category);

        // Search by partial code
        $response = $this->actingAs($user)->get('/pos?search=BC-12345');

        $response->assertOk();
        // Should find the product by its code
        $response->assertSee('Scannable Product', false);
    }

    public function test_pos_search_finds_product_by_partial_name(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create products
        $product1 = $this->createProductWithCode('Apple iPhone', 'IPHONE-001', $category);
        $product2 = $this->createProductWithCode('Samsung Galaxy', 'GALAXY-001', $category);
        $product3 = $this->createProductWithCode('Google Pixel', 'PIXEL-001', $category);

        // Search by partial name
        $response = $this->actingAs($user)->get('/pos?search=Apple');

        $response->assertOk();
        $response->assertSee('Apple iPhone', false);
        $response->assertDontSee('Samsung Galaxy', false);
        $response->assertDontSee('Google Pixel', false);
    }

    public function test_pos_search_returns_multiple_results_when_matching(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create products with similar names
        $product1 = $this->createProductWithCode('MacBook Pro', 'MBP-001', $category);
        $product2 = $this->createProductWithCode('MacBook Air', 'MBA-001', $category);
        $product3 = $this->createProductWithCode('Windows Laptop', 'WIN-001', $category);

        // Search should find both MacBooks
        $response = $this->actingAs($user)->get('/pos?search=MacBook');

        $response->assertOk();
        $response->assertSee('MacBook Pro', false);
        $response->assertSee('MacBook Air', false);
        $response->assertDontSee('Windows Laptop', false);
    }

    public function test_pos_search_returns_empty_when_no_match(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create products
        $this->createProductWithCode('Product A', 'CODE-001', $category);
        $this->createProductWithCode('Product B', 'CODE-002', $category);

        // Search for something that doesn't exist
        $response = $this->actingAs($user)->get('/pos?search=NonexistentProduct');

        $response->assertOk();
        $response->assertDontSee('Product A', false);
        $response->assertDontSee('Product B', false);
    }

    public function test_pos_search_is_case_insensitive(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create product with mixed case
        $product = $this->createProductWithCode('Test Product', 'TEST-CODE', $category);

        // Search with different cases
        $response1 = $this->actingAs($user)->get('/pos?search=test');
        $response2 = $this->actingAs($user)->get('/pos?search=TEST');
        $response3 = $this->actingAs($user)->get('/pos?search=Test');

        $response1->assertOk();
        $response1->assertSee('Test Product', false);

        $response2->assertOk();
        $response2->assertSee('Test Product', false);

        $response3->assertOk();
        $response3->assertSee('Test Product', false);
    }

    public function test_pos_search_works_with_barcode_scanner_format(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create product with barcode-like code
        $product = $this->createProductWithCode('Scanned Product', '1234567890123', $category);

        // Search using the full barcode
        $response = $this->actingAs($user)->get('/pos?search=1234567890123');

        $response->assertOk();
        $response->assertSee('Scanned Product', false);
    }

    public function test_pos_search_excludes_expired_products(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create expired product
        $expiredProduct = Product::factory()->create([
            'name' => 'Expired Product',
            'code' => 'EXPIRED-001',
            'category_id' => $category->id,
            'expire_date' => Carbon::now()->subDay(), // Expired yesterday
        ]);

        // Create valid product
        $validProduct = $this->createProductWithCode('Valid Product', 'VALID-001', $category);

        // Search should only return valid product
        $response = $this->actingAs($user)->get('/pos?search=Product');

        $response->assertOk();
        $response->assertSee('Valid Product', false);
        $response->assertDontSee('Expired Product', false);
    }

    public function test_pos_search_combines_with_category_filter(): void
    {
        $user = $this->createAuthenticatedUser();
        $category1 = $this->createCategory();
        $category2 = Category::factory()->create();

        // Create products in different categories with similar names
        $product1 = $this->createProductWithCode('Laptop A', 'LAP-001', $category1);
        $product2 = $this->createProductWithCode('Laptop B', 'LAP-002', $category2);

        // Search with category filter - should only find product in category1
        $response = $this->actingAs($user)->get("/pos?search=Laptop&category_id={$category1->id}");

        $response->assertOk();
        // Should find Laptop A which is in category1
        $response->assertSee('Laptop A', false);
    }

    public function test_pos_search_field_has_correct_placeholder(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk();
        $response->assertSee('Search by name or barcode...', false);
    }

    public function test_product_search_matches_category_name(): void
    {
        $category = $this->createCategory();
        $product = $this->createProductWithCode('Category Search Product', 'CATEGORY-SEARCH-001', $category);

        $productsByName = Product::filter(['search' => $product->name])->get();
        $products = Product::filter(['search' => $category->name])->get();

        $this->assertTrue($productsByName->contains('id', $product->id));
        $this->assertTrue($products->contains('id', $product->id));
    }

    public function test_pos_search_returns_json_product_fragment(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'name' => 'AJAX Search Product',
            'code' => 'AJAX-SEARCH-001',
            'category_id' => $category->id,
            'stock' => 5,
            'expire_date' => Carbon::now()->addYear(),
        ]);

        $response = $this->actingAs($user)->getJson('/pos?search=AJAX+Search+Product');

        $response->assertOk()
            ->assertJsonStructure(['html', 'total'])
            ->assertJsonPath('total', 1);
        $this->assertStringContainsString($product->name, $response->json('html'));
    }

    public function test_pos_does_not_show_products_before_a_search(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'name' => 'Initial Catalog Product',
            'code' => 'INITIAL-CATALOG-001',
            'category_id' => $category->id,
            'stock' => 5,
            'expire_date' => Carbon::now()->addYear(),
        ]);

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk()
            ->assertDontSee($product->name, false)
            ->assertSee('Search by product name, barcode, or category to view products.', false);
    }

    public function test_updating_cart_discount_refreshes_net_amount(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $item = Cart::add([
            'id' => 'discount-test',
            'name' => 'Discount Test Item',
            'qty' => 2,
            'price' => 100,
            'options' => [
                'original_price' => 100,
                'tax' => 0,
                'discount' => 0,
                'currency' => 'PKR',
                'manual' => true,
            ],
        ]);

        $response = $this->actingAs($user)->postJson("/pos/discount/{$item->rowId}", [
            'discount' => 25,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
        $this->assertStringContainsString('PKR 195.00', $response->json('cart_html'));
        $cartHtml = $response->json('cart_html');
        $this->assertMatchesRegularExpression('/class="[^"]*pos-summary-price"\s+value="PKR 100\.00"/', $cartHtml);
        $this->assertMatchesRegularExpression('/class="[^"]*pos-summary-tax"\s+value="PKR 20\.00"/', $cartHtml);
        $this->assertMatchesRegularExpression('/class="[^"]*pos-summary-discount"\s+value="PKR 25\.00"/', $cartHtml);
        $updatedItem = Cart::content()->first();
        $this->assertSame(25.0, (float) $updatedItem->options->discount);
        $this->assertEquals(195.0, (float) Cart::total(null, null, ''));

        $quantityResponse = $this->actingAs($user)->postJson("/pos/update/{$updatedItem->rowId}", [
            'qty' => 3,
        ]);

        $quantityResponse->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('PKR 305.00', $quantityResponse->json('cart_html'));
        $this->assertEquals(305.0, (float) Cart::total(null, null, ''));
        $this->assertSame(25.0, (float) Cart::content()->first()->options->discount);
    }

    public function test_adding_product_uses_its_gst_rate_in_the_cart(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $product = $this->createProductWithCode('GST Rate Item', 'GST-RATE-001');
        $product->update([
            'selling_price' => 100,
            'order_tax' => 17.5,
        ]);

        $response = $this->actingAs($user)->postJson('/pos/add', [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 100,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $cartItem = Cart::content()->first();
        $this->assertSame(17.5, (float) $cartItem->options->tax_rate);
        $this->assertSame(17.5, (float) $cartItem->taxRate);
        $this->assertEquals(17.5, (float) Cart::tax(2, '.', ''));
        $this->assertStringContainsString('value="17.50"', $response->json('cart_html'));
        $this->assertStringContainsString('class="pos-tax-amount">17.50</td>', $response->json('cart_html'));
    }

    public function test_updates_for_removed_cart_rows_return_the_current_cart(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();

        $requests = [
            ['/pos/update/%s', ['qty' => 2]],
            ['/pos/discount/%s', ['discount' => 10]],
            ['/pos/tax-rate/%s', ['tax_rate' => 18]],
        ];

        foreach ($requests as [$endpoint, $payload]) {
            $item = Cart::add([
                'id' => uniqid('removed-row-', true),
                'name' => 'Removed Cart Item',
                'qty' => 1,
                'price' => 100,
                'options' => ['original_price' => 100, 'currency' => 'PKR'],
            ]);
            Cart::remove($item->rowId);

            $response = $this->actingAs($user)->postJson(sprintf($endpoint, $item->rowId), $payload);

            $response->assertStatus(409)
                ->assertJsonPath('success', false)
                ->assertJsonStructure(['cart_html', 'cart_count']);
        }
    }

    public function test_cart_quantity_is_limited_to_current_product_stock(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $product = $this->createProductWithCode('Stock Limit Item', 'STOCK-LIMIT-001');
        $product->update(['stock' => 5]);
        $item = Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100,
            'options' => [
                'original_price' => 100,
                'currency' => 'PKR',
                'stock' => $product->stock,
            ],
        ]);

        $response = $this->actingAs($user)->postJson("/pos/update/{$item->rowId}", [
            'qty' => 8,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $updatedItem = Cart::content()->first();
        $this->assertSame(5, (int) $updatedItem->qty);
        $this->assertSame(5, (int) $updatedItem->options->stock);
        $this->assertStringContainsString('max="5"', $response->json('cart_html'));
    }

    public function test_updating_cart_tax_rate_persists_and_recalculates_tax(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.pos'],
            ['group_name' => 'pos']
        );
        Role::where('name', 'test-role')->first()->givePermissionTo($permission);
        $user = $this->createAuthenticatedUser();
        $item = Cart::add([
            'id' => 'tax-rate-test',
            'name' => 'Tax Rate Test Item',
            'qty' => 2,
            'price' => 100,
            'options' => [
                'original_price' => 100,
                'discount' => 0,
                'currency' => 'PKR',
            ],
        ]);

        $response = $this->actingAs($user)->postJson("/pos/tax-rate/{$item->rowId}", [
            'tax_rate' => 18,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $updatedItem = Cart::content()->first();
        $this->assertSame(18.0, (float) $updatedItem->options->tax_rate);
        $this->assertSame(18.0, (float) $updatedItem->taxRate);
        $this->assertEquals(36.0, (float) Cart::tax(2, '.', ''));
        $this->assertStringContainsString('value="18.00"', $response->json('cart_html'));
        $this->assertStringContainsString('class="pos-tax-amount">36.00</td>', $response->json('cart_html'));
    }
}
