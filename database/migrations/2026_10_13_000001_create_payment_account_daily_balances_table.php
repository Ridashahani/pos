<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_account_daily_balances')) {
            Schema::create('payment_account_daily_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_account_id')->constrained('payment_accounts')->cascadeOnDelete();
                $table->date('balance_date');
                $table->decimal('opening_balance', 15, 2);
                $table->decimal('closing_balance', 15, 2);
                $table->timestamps();
                $table->unique(['payment_account_id', 'balance_date'], 'acct_daily_balance_date_unique');
            });

            return;
        }

        Schema::table('payment_account_daily_balances', function (Blueprint $table) {
            $table->unique(['payment_account_id', 'balance_date'], 'acct_daily_balance_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_account_daily_balances');
    }
};
