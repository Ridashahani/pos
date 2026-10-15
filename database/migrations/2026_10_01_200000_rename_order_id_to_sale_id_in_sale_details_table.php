<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sale_details', 'order_id') && ! Schema::hasColumn('sale_details', 'sale_id')) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->renameColumn('order_id', 'sale_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_details', 'sale_id') && ! Schema::hasColumn('sale_details', 'order_id')) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->renameColumn('sale_id', 'order_id');
            });
        }
    }
};