<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stock_transfer_edit_route_loads_existing_batch_even_when_it_has_zero_remaining_quantity(): void
    {
        $role = Role::firstOrCreate(['name' => 'stock-transfer-role']);
        $accessPermission = Permission::firstOrCreate(
            ['name' => 'access.stocks'],
            ['group_name' => 'stocks']
        );
        $editPermission = Permission::firstOrCreate(
            ['name' => 'edit.stocks'],
            ['group_name' => 'stocks']
        );
        $role->givePermissionTo([$accessPermission, $editPermission]);

        $branch = Branch::create([
            'name' => 'Transfer Edit Branch',
            'address' => '123 Test Street',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->branches()->attach($branch->id);
        $user->update(['active_branch_id' => $branch->id]);

        $category = Category::factory()->create();
        $product = Product::create([
            'name' => 'Transfer Product',
            'slug' => 'transfer-product',
            'code' => 'TRANSFER-001',
            'category_id' => $category->id,
            'stock' => 5,
            'selling_price' => 100,
            'cost_price' => 60,
        ]);

        $stockIn = StockIn::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'batch_no' => 'BATCH-EDIT-001',
            'quantity' => 5,
            'remaining_quantity' => 0,
            'cost_price' => 60,
        ]);

        $transfer = \App\Models\StockTransfer::create([
            'reference' => 'TRF-001',
            'product_id' => $product->id,
            'stock_in_id' => $stockIn->id,
            'quantity' => 2,
            'from_branch_id' => $branch->id,
            'to_branch_id' => $branch->id,
            'status' => 'Pending',
        ]);

        $this->actingAs($user)
            ->get(route('stock.transfer.edit', $transfer))
            ->assertOk();
    }

    public function test_replenished_product_is_removed_from_out_of_stock_list_until_stock_runs_out_again(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'access.stocks'],
            ['group_name' => 'stocks']
        );
        $role = Role::firstOrCreate(['name' => 'stock-test-role']);
        $role->givePermissionTo($permission);

        $branch = Branch::create([
            'name' => 'Stock Test Branch',
            'address' => '123 Test Street',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->branches()->attach($branch->id);
        $user->update(['active_branch_id' => $branch->id]);

        $category = Category::factory()->create();
        $product = Product::create([
            'name' => 'Replenished Product',
            'slug' => 'replenished-product',
            'code' => 'REPLENISHED-001',
            'category_id' => $category->id,
            'stock' => 4,
            'selling_price' => 100,
        ]);
        $supplier = Supplier::create([
            'name' => 'Stock Test Supplier',
            'email' => 'stock-test@example.com',
            'phone' => '03001234567',
        ]);

        $createPurchaseBatch = function (string $purchaseNo, int $quantity) use ($branch, $product, $supplier): StockIn {
            $purchase = Purchase::create([
                'purchase_no' => $purchaseNo,
                'supplier_id' => $supplier->id,
                'branch_id' => $branch->id,
                'purchase_date' => now()->toDateString(),
                'total_amount' => $quantity * 100,
                'paid_amount' => 0,
                'due_amount' => $quantity * 100,
                'payment_status' => 'due',
            ]);

            return StockIn::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'variation_details' => [],
                'branch_id' => $branch->id,
                'batch_no' => $purchaseNo,
                'quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'cost_price' => 100,
            ]);
        };

        $depletedBatch = $createPurchaseBatch('PUR-OUT-001', 2);
        $depletedBatch->update(['remaining_quantity' => 0]);
        $createPurchaseBatch('PUR-RESTOCK-001', 4);

        $this->actingAs($user)->get(route('stock.out-of-stock'))
            ->assertOk()
            ->assertDontSee('Replenished Product');

        $product->update(['stock' => 0]);

        $this->actingAs($user)->get(route('stock.out-of-stock'))
            ->assertOk()
            ->assertSee('Replenished Product');
    }
}
