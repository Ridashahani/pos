<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['today_balance', 'balance'] as $column) {
            if (Schema::hasColumn('payment_accounts', $column)) {
                Schema::table('payment_accounts', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        if (
            !Schema::hasColumn('payment_accounts', 'today_balance')
            && !Schema::hasColumn('payment_accounts', 'balance')
        ) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->decimal('today_balance', 15, 2)->default(0);
            });
        }
    }
};
