<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'status' => 'sale_status',
            'subtotal' => 'sub_total',
            'tax' => 'vat',
            'grand_total' => 'total',
            'paid_amount' => 'pay_amount',
            'payment_method' => 'payment_type',
        ];

        foreach ($columns as $oldName => $newName) {
            if (Schema::hasColumn('sales', $oldName) && ! Schema::hasColumn('sales', $newName)) {
                Schema::table('sales', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }

        if (! Schema::hasColumn('sales', 'total_products')) {
            Schema::table('sales', function (Blueprint $table): void {
                $table->unsignedInteger('total_products')->default(0);
            });
        }

        Schema::table('sales', function (Blueprint $table): void {
            $table->string('sale_status')->default('pending')->change();
            $table->unsignedBigInteger('branch_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        DB::table('sales')
            ->where('sale_status', 'completed')
            ->update(['sale_status' => 'complete']);
    }

    public function down(): void
    {
        DB::table('sales')
            ->where('sale_status', 'complete')
            ->update(['sale_status' => 'completed']);

        Schema::table('sales', function (Blueprint $table): void {
            $table->enum('sale_status', ['pending', 'completed', 'returned'])
                ->default('pending')
                ->change();
        });

        if (Schema::hasColumn('sales', 'total_products')) {
            Schema::table('sales', function (Blueprint $table): void {
                $table->dropColumn('total_products');
            });
        }

        $columns = [
            'sale_status' => 'status',
            'sub_total' => 'subtotal',
            'vat' => 'tax',
            'total' => 'grand_total',
            'pay_amount' => 'paid_amount',
            'payment_type' => 'payment_method',
        ];

        foreach ($columns as $oldName => $newName) {
            if (Schema::hasColumn('sales', $oldName) && ! Schema::hasColumn('sales', $newName)) {
                Schema::table('sales', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }
    }
};