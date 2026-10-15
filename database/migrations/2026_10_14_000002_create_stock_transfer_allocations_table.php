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

                $table->index(['stock_transfer_id', 'source_stock_in_id'], 'sta_transfer_source_idx');
            });
        }

        DB::table('stock_transfers')
            ->whereNotNull('destination_stock_in_id')
            ->whereNotNull('stock_in_id')
            ->orderBy('id')
            ->chunkById(500, function ($transfers): void {
                foreach ($transfers as $transfer) {
                    DB::table('stock_transfer_allocations')->insert([
                        'stock_transfer_id' => $transfer->id,
                        'source_stock_in_id' => $transfer->stock_in_id,
                        'destination_stock_in_id' => $transfer->destination_stock_in_id,
                        'quantity' => $transfer->quantity,
                        'created_at' => $transfer->created_at,
                        'updated_at' => $transfer->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_allocations');
    }
};
