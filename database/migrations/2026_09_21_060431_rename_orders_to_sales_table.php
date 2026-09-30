<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    if (Schema::hasTable('orders') && !Schema::hasTable('sales')) {
        Schema::rename('orders', 'sales');

        if (Schema::hasColumn('sales', 'order_date')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->renameColumn('order_date', 'sale_date');
            });
        }

        if (Schema::hasColumn('sales', 'order_status')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->renameColumn('order_status', 'sale_status');
            });
        }
    }

    if (Schema::hasTable('order_details') && !Schema::hasTable('sale_details')) {
        Schema::rename('order_details', 'sale_details');
    }

    if (Schema::hasTable('sale_details') && Schema::hasColumn('sale_details', 'order_id')) {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->renameColumn('order_id', 'sale_id');
        });
    }
}

public function down(): void
{
    if (!Schema::hasTable('sales') || !Schema::hasColumn('sales', 'sale_status')) {
        return;
    }

    if (Schema::hasTable('sale_details') && Schema::hasColumn('sale_details', 'sale_id')) {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->renameColumn('sale_id', 'order_id');
        });
    }

    if (Schema::hasColumn('sales', 'sale_status')) {
    Schema::table('sales', function (Blueprint $table) {
        $table->renameColumn('sale_status', 'order_status');
    });
    }

    if (Schema::hasColumn('sales', 'sale_date')) {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('sale_date', 'order_date');
        });
    }

    if (Schema::hasTable('sale_details') && !Schema::hasTable('order_details')) {
        Schema::rename('sale_details', 'order_details');
    }

    if (Schema::hasTable('sales') && !Schema::hasTable('orders')) {
        Schema::rename('sales', 'orders');
    }
}
};
