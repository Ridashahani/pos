<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockIn;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
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
        $permissions = collect(['access.stocks', 'delete.stocks'])
            ->map(fn (string $name) => Permission::firstOrCreate(
                ['name' => $name],
                ['group_name' => 'stocks']
            ));
        $role = Role::firstOrCreate(['name' => 'stock-test-role']);
        $role->syncPermissions($permissions);

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
        $secondDepletedBatch = $createPurchaseBatch('PUR-OUT-002', 1);
        $secondDepletedBatch->update(['remaining_quantity' => 0]);
        $createPurchaseBatch('PUR-RESTOCK-001', 4);

        $this->actingAs($user)->get(route('stock.out-of-stock'))
            ->assertOk()
            ->assertDontSee('Replenished Product');

        $product->update(['stock' => 0]);

        $this->actingAs($user)->get(route('stock.out-of-stock'))
            ->assertOk()
            ->assertSee('Replenished Product')
            ->assertSee('Stock Test Branch')
            ->assertSee('<th>Branches</th>', false)
            ->assertDontSee('<th>From</th>', false)
            ->assertDontSee('<th>To</th>', false)
            ->assertDontSee('<th>Status</th>', false);

        $this->delete(route('stock.out-of-stock.destroy', $depletedBatch))
            ->assertRedirect(route('stock.out-of-stock'));
        $this->assertDatabaseMissing('stock_in', ['id' => $depletedBatch->id]);
        $this->assertDatabaseMissing('stock_in', ['id' => $secondDepletedBatch->id]);
    }

    public function test_completed_transfer_creates_a_stock_in_batch_at_the_destination_branch(): void
    {
        $permissions = collect(['access.stocks', 'create.stocks', 'edit.stocks', 'delete.stocks'])
            ->map(fn (string $name) => Permission::firstOrCreate(
                ['name' => $name],
                ['group_name' => 'stocks']
            ));
        $role = Role::firstOrCreate(['name' => 'stock-transfer-test-role']);
        $role->syncPermissions($permissions);

        $sourceBranch = Branch::create([
            'name' => 'Transfer Source Branch',
            'address' => '123 Source Street',
        ]);
        $destinationBranch = Branch::create([
            'name' => 'Transfer Destination Branch',
            'address' => '456 Destination Street',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->branches()->attach([$sourceBranch->id, $destinationBranch->id]);
        $user->update(['active_branch_id' => $sourceBranch->id]);

        $category = Category::factory()->create();
        $product = Product::create([
            'name' => 'Transferred Product',
            'slug' => 'transferred-product',
            'code' => 'TRANSFER-001',
            'category_id' => $category->id,
            'stock' => 8,
            'selling_price' => 150,
        ]);
        $supplier = Supplier::create([
            'name' => 'Transfer Test Supplier',
            'email' => 'transfer-test@example.com',
            'phone' => '03001234568',
        ]);
        $purchase = Purchase::create([
            'purchase_no' => 'PUR-TRANSFER-001',
            'supplier_id' => $supplier->id,
            'branch_id' => $sourceBranch->id,
            'purchase_date' => now()->toDateString(),
            'total_amount' => 800,
            'paid_amount' => 0,
            'due_amount' => 800,
            'payment_status' => 'due',
        ]);
        $sourceBatch = StockIn::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'variation_details' => [['name' => 'Size', 'value' => 'Large']],
            'branch_id' => $sourceBranch->id,
            'batch_no' => 'PUR-TRANSFER-001',
            'quantity' => 8,
            'remaining_quantity' => 8,
            'cost_price' => 100,
        ]);
        $secondSourceBatch = StockIn::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'variation_details' => [['name' => 'Size', 'value' => 'Large']],
            'branch_id' => $sourceBranch->id,
            'batch_no' => 'PUR-TRANSFER-002',
            'quantity' => 4,
            'remaining_quantity' => 4,
            'cost_price' => 100,
        ]);
        $destinationBatch = StockIn::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'variation_details' => [['name' => 'Size', 'value' => 'Large']],
            'branch_id' => $destinationBranch->id,
            'batch_no' => 'PUR-DESTINATION-001',
            'quantity' => 2,
            'remaining_quantity' => 2,
            'cost_price' => 100,
        ]);

        $response = $this->actingAs($user)->post(route('stock.transfer.store'), [
            'stock_in_id' => $sourceBatch->id,
            'quantity' => 3,
            'to_branch_id' => $destinationBranch->id,
        ]);

        $response->assertRedirect(route('stock.transfer'));
        $transfer = StockTransfer::where('stock_in_id', $sourceBatch->id)->firstOrFail();
        DB::table('stock_transfer_allocations')
            ->where('stock_transfer_id', $transfer->id)
            ->delete();
        $transfer->update([
            'destination_stock_in_id' => null,
            'status' => 'In Transit',
        ]);
        DB::table('stock_in')
            ->where('id', $destinationBatch->id)
            ->update(['quantity' => 2, 'remaining_quantity' => 2]);
        $product->update(['stock' => 5]);

        $this->post(route('stock.transfer.store'), [
            'stock_in_id' => $secondSourceBatch->id,
            'quantity' => 1,
            'to_branch_id' => $destinationBranch->id,
        ])->assertRedirect(route('stock.transfer'));
        $transfer->refresh();
        $destinationBatch->refresh();

        $this->assertSame('Completed', $transfer->status);
        $this->assertSame(1, StockTransfer::where('product_id', $product->id)
            ->where('from_branch_id', $sourceBranch->id)
            ->where('to_branch_id', $destinationBranch->id)
            ->count());
        $this->assertSame(4, (int) $transfer->quantity);
        $this->assertSame(2, (int) $transfer->allocations()->count());
        $this->assertSame($destinationBatch->id, $transfer->destination_stock_in_id);
        $this->assertSame('PUR-DESTINATION-001', $destinationBatch->batch_no);
        $this->assertSame(6, (int) $destinationBatch->quantity);
        $this->assertSame(6, (int) $destinationBatch->remaining_quantity);
        $this->assertSame(100.0, (float) $destinationBatch->cost_price);
        $this->assertSame([['name' => 'Size', 'value' => 'Large']], $destinationBatch->variation_details);
        $this->assertSame(5, (int) $sourceBatch->fresh()->remaining_quantity);
        $this->assertSame(3, (int) $secondSourceBatch->fresh()->remaining_quantity);
        $this->assertSame(8, (int) $product->fresh()->stock);

        $user->update(['active_branch_id' => $destinationBranch->id]);
        $stockInResponse = $this->get(route('stock.in'));
        $stockInResponse
            ->assertOk()
            ->assertSee('Transferred Product')
            ->assertSee('Transfer Destination Branch')
            ->assertSee('6');
        $this->assertSame(2, substr_count($stockInResponse->getContent(), 'Transferred Product'));
        $this->get(route('stock.transfer'))
            ->assertOk()
            ->assertSee('Completed')
            ->assertSee('Size: Large')
            ->assertSee('Transfer Source Branch')
            ->assertSee('Transfer Destination Branch')
            ->assertSee(route('stock.transfer.edit', $transfer));
        $this->get(route('stock.in.details', $destinationBatch->id))
            ->assertOk()
            ->assertSee($transfer->reference)
            ->assertSee('Transfer Source Branch')
            ->assertSee('<td>6</td>', false);

        $this->put(route('stock.transfer.update', $transfer), [
            'stock_in_id' => $sourceBatch->id,
            'quantity' => 2,
            'to_branch_id' => $destinationBranch->id,
        ])->assertRedirect(route('stock.transfer'));

        $updatedDestinationBatch = $destinationBatch->fresh();
        $this->assertSame(4, (int) $updatedDestinationBatch->quantity);
        $this->assertSame(4, (int) $updatedDestinationBatch->remaining_quantity);
        $this->assertSame(6, (int) $sourceBatch->fresh()->remaining_quantity);
        $this->assertSame(4, (int) $secondSourceBatch->fresh()->remaining_quantity);

        $this->delete(route('stock.transfer.destroy', $transfer))
            ->assertRedirect(route('stock.transfer'))
            ->assertSessionHas('success', 'Stock transfer deleted successfully.');

        $this->assertSame(2, (int) $destinationBatch->fresh()->quantity);
        $this->assertSame(2, (int) $destinationBatch->fresh()->remaining_quantity);
        $this->assertSame(8, (int) $sourceBatch->fresh()->remaining_quantity);
        $this->assertSame(4, (int) $secondSourceBatch->fresh()->remaining_quantity);
        $this->assertSame(8, (int) $product->fresh()->stock);
    }
}
