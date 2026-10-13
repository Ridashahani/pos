<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn('payment_accounts', 'balance')
            && !Schema::hasColumn('payment_accounts', 'today_balance')
        ) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->renameColumn('balance', 'today_balance');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn('payment_accounts', 'today_balance')
            && !Schema::hasColumn('payment_accounts', 'balance')
        ) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->renameColumn('today_balance', 'balance');
            });
        }
    }
};
