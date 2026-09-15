<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('category_id');
            $table->string('barcode_symbology')->nullable()->after('code');
            $table->string('product_unit')->nullable();
            $table->string('sale_unit')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->unsignedInteger('quantity_limit')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('warehouse')->nullable();
            $table->string('status')->default('received');
            $table->text('note')->nullable();
            $table->string('product_type')->default('single');
            $table->string('variation')->nullable();
            $table->json('variation_types')->nullable();
            $table->string('variation_type')->nullable();
            $table->decimal('product_cost', 12, 2)->nullable();
            $table->decimal('product_price', 12, 2)->nullable();
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->decimal('special_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_alert')->default(0);
            $table->decimal('order_tax', 8, 2)->default(0);
            $table->string('tax_type')->nullable();
            $table->unsignedInteger('add_product_quantity')->default(0);
            $table->json('images')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'brand', 'barcode_symbology', 'product_unit', 'sale_unit', 'purchase_unit',
                'quantity_limit', 'supplier_id', 'warehouse', 'status', 'note', 'product_type',
                'variation', 'variation_types', 'variation_type', 'product_cost', 'product_price',
                'wholesale_price', 'special_price', 'stock_alert', 'order_tax', 'tax_type',
                'add_product_quantity', 'images',
            ]);
        });
    }
};