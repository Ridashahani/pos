<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stock_in')
            ->whereNull('variation_id')
            ->orderBy('id')
            ->chunkById(500, function ($stockIns): void {
                foreach ($stockIns as $stockIn) {
                    $purchaseItem = DB::table('purchase_items')
                        ->where('purchase_id', $stockIn->purchase_id)
                        ->where('product_id', $stockIn->product_id)
                        ->orderBy('id')
                        ->first();

                    $selectedVariations = json_decode($purchaseItem->variations ?? '[]', true) ?: [];
                    $variationId = $selectedVariations[0]['variation_id'] ?? null;

                    if (! $variationId) {
                        $variationId = DB::table('products')
                            ->where('id', $stockIn->product_id)
                            ->value('variation_id');
                    }

                    if ($variationId && DB::table('variations')->where('id', $variationId)->exists()) {
                        DB::table('stock_in')->where('id', $stockIn->id)->update([
                            'variation_id' => $variationId,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Keep variation IDs because the migration cannot distinguish backfilled values from later edits.
    }
};