<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn('payment_accounts', 'opening_balance')
            && !Schema::hasColumn('payment_accounts', 'current_balance')
        ) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->renameColumn('opening_balance', 'current_balance');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn('payment_accounts', 'current_balance')
            && !Schema::hasColumn('payment_accounts', 'opening_balance')
        ) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->renameColumn('current_balance', 'opening_balance');
            });
        }
    }
};
