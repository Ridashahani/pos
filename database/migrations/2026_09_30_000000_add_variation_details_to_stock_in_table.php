<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in', function (Blueprint $table): void {
            $table->json('variation_details')->nullable()->after('variation_id');
        });

        DB::table('stock_in')->orderBy('id')->chunkById(500, function ($stockIns): void {
            foreach ($stockIns as $stockIn) {
                $stockInIndex = DB::table('stock_in')
                    ->where('purchase_id', $stockIn->purchase_id)
                    ->where('product_id', $stockIn->product_id)
                    ->where('id', '<', $stockIn->id)
                    ->count();
                $purchaseItem = DB::table('purchase_items')
                    ->where('purchase_id', $stockIn->purchase_id)
                    ->where('product_id', $stockIn->product_id)
                    ->orderBy('id')
                    ->skip($stockInIndex)
                    ->first();

                if ($purchaseItem) {
                    $variationDetails = json_decode($purchaseItem->variations ?? '[]', true) ?: [];

                    DB::table('stock_in')->where('id', $stockIn->id)->update([
                        'variation_details' => json_encode($variationDetails),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_in', function (Blueprint $table): void {
            $table->dropColumn('variation_details');
        });
    }
};