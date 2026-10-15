<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->foreignId('destination_stock_in_id')
                ->nullable()
                ->after('stock_in_id')
                ->constrained('stock_in')
                ->nullOnDelete();
        });

        DB::table('stock_transfers')->orderBy('id')->chunkById(500, function ($transfers): void {
            foreach ($transfers as $transfer) {
                $destinationStockIn = DB::table('stock_in')
                    ->where('batch_no', $transfer->reference)
                    ->where('product_id', $transfer->product_id)
                    ->where('branch_id', $transfer->to_branch_id)
                    ->first();

                if ($destinationStockIn) {
                    DB::table('stock_transfers')
                        ->where('id', $transfer->id)
                        ->update([
                            'destination_stock_in_id' => $destinationStockIn->id,
                            'status' => 'Completed',
                        ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('destination_stock_in_id');
        });
    }
};
