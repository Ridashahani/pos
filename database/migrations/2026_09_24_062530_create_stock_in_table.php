<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('imei')->nullable()->index();
            $table->string('condition')->default('new');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('remaining_qty');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['product_id', 'remaining_qty']);
        });

        DB::table('purchase_items')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->select([
                'purchase_items.purchase_id',
                'purchase_items.product_id',
                'products.imei',
                'purchase_items.quantity',
                'purchase_items.unit_price',
                'products.selling_price',
                'purchase_items.created_at',
                'purchase_items.updated_at',
            ])
            ->orderBy('purchase_items.id')
            ->get()
            ->each(function (object $item): void {
                DB::table('stock_in')->insert([
                    'purchase_id' => $item->purchase_id,
                    'product_id' => $item->product_id,
                    'imei' => $item->imei,
                    'condition' => 'new',
                    'quantity' => $item->quantity,
                    'remaining_qty' => $item->quantity,
                    'cost_price' => $item->unit_price,
                    'sale_price' => $item->selling_price ?? 0,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};
