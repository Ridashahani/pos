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

        DB::table('sales')
            ->where('status', 'complete')
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('sales')
            ->where('status', 'completed')
            ->update(['status' => 'complete']);

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
    }
};