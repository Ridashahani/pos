<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_transfer_allocations')) {
            Schema::create('stock_transfer_allocations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('source_stock_in_id')->constrained('stock_in')->restrictOnDelete();
                $table->foreignId('destination_stock_in_id')->constrained('stock_in')->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('stock_transfer_allocations', 'sta_transfer_source_index')) {
            Schema::table('stock_transfer_allocations', function (Blueprint $table): void {
                $table->index(['stock_transfer_id', 'source_stock_in_id'], 'sta_transfer_source_index');
            });
        }

        DB::table('stock_transfers')
            ->whereNotNull('stock_in_id')
            ->orderBy('id')
            ->chunkById(500, function ($transfers): void {
                foreach ($transfers as $transfer) {
                    DB::transaction(function () use ($transfer): void {
                        if (DB::table('stock_transfer_allocations')
                            ->where('stock_transfer_id', $transfer->id)
                            ->exists()) {
                            return;
                        }

                        $sourceStockIn = DB::table('stock_in')->where('id', $transfer->stock_in_id)->first();

                        if (! $sourceStockIn) {
                            throw new RuntimeException("Cannot migrate stock transfer {$transfer->id}: its source stock-in record is missing.");
                        }

                        $destinationStockInId = DB::table('stock_in')->insertGetId([
                            'purchase_id' => $sourceStockIn->purchase_id,
                            'product_id' => $transfer->product_id,
                            'variation_id' => $sourceStockIn->variation_id,
                            'variation_details' => $sourceStockIn->variation_details,
                            'branch_id' => $transfer->to_branch_id,
                            'batch_no' => 'TRF-'.$transfer->reference,
                            'quantity' => $transfer->quantity,
                            'remaining_quantity' => $transfer->quantity,
                            'cost_price' => $sourceStockIn->cost_price,
                            'created_at' => $transfer->created_at,
                            'updated_at' => $transfer->updated_at,
                        ]);

                        DB::table('stock_transfer_allocations')->insert([
                            'stock_transfer_id' => $transfer->id,
                            'source_stock_in_id' => $sourceStockIn->id,
                            'destination_stock_in_id' => $destinationStockInId,
                            'quantity' => $transfer->quantity,
                            'created_at' => $transfer->created_at,
                            'updated_at' => $transfer->updated_at,
                        ]);

                        DB::table('products')
                            ->where('id', $transfer->product_id)
                            ->increment('stock', $transfer->quantity);

                        DB::table('stock_transfers')
                            ->where('id', $transfer->id)
                            ->update(['status' => 'Completed']);
                    });
                }
            });
    }

    public function down(): void
    {
        if (DB::table('stock_transfer_allocations')
            ->select('stock_transfer_id')
            ->groupBy('stock_transfer_id')
            ->havingRaw('COUNT(DISTINCT source_stock_in_id) > 1')
            ->exists()) {
            throw new RuntimeException('Cannot roll back stock transfer allocations after a transfer has combined multiple source batches.');
        }

        $destinationStockInIds = DB::table('stock_transfer_allocations')->pluck('destination_stock_in_id');

        if (DB::table('stock_transfer_allocations as allocations')
            ->join('stock_in as destination', 'destination.id', '=', 'allocations.destination_stock_in_id')
            ->whereColumn('destination.quantity', '!=', 'allocations.quantity')
            ->orWhereColumn('destination.remaining_quantity', '!=', 'allocations.quantity')
            ->exists()) {
            throw new RuntimeException('Cannot roll back stock transfer allocations after received stock has been used.');
        }

        if (DB::table('sold_items')->whereIn('stock_in_id', $destinationStockInIds)->exists()
            || DB::table('stock_transfer_allocations')->whereIn('source_stock_in_id', $destinationStockInIds)->exists()) {
            throw new RuntimeException('Cannot roll back stock transfer allocations because received stock has been sold or transferred again.');
        }

        $allocationTotals = DB::table('stock_transfer_allocations as allocations')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'allocations.stock_transfer_id')
            ->select('stock_transfers.product_id')
            ->selectRaw('SUM(allocations.quantity) as quantity')
            ->groupBy('stock_transfers.product_id')
            ->get();

        foreach ($allocationTotals as $allocationTotal) {
            $product = DB::table('products')->where('id', $allocationTotal->product_id)->first();
            if (! $product || $product->stock < $allocationTotal->quantity) {
                throw new RuntimeException('Cannot roll back stock transfer allocations because product stock has changed.');
            }
        }

        DB::table('stock_transfer_allocations')->delete();
        DB::table('stock_in')->whereIn('id', $destinationStockInIds)->delete();

        foreach ($allocationTotals as $allocationTotal) {
            DB::table('products')
                ->where('id', $allocationTotal->product_id)
                ->decrement('stock', $allocationTotal->quantity);
        }

        Schema::dropIfExists('stock_transfer_allocations');
    }
};
