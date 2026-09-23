<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'barcode_symbology',
                'product_unit',
                'sale_unit',
                'purchase_unit',
                'quantity_limit',
                'warehouse',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode_symbology')->nullable();
            $table->string('product_unit')->nullable();
            $table->string('sale_unit')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->unsignedInteger('quantity_limit')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('status')->default('received');
        });
    }
};