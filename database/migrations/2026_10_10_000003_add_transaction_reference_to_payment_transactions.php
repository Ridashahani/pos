<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function ($table) {
            $table->string('transaction_id', 50)->nullable()->unique();
        });

        DB::table('payment_transactions')
            ->select(['id', 'transaction_date'])
            ->orderBy('id')
            ->each(function (object $transaction): void {
                $date = \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('Ymd');
                $reference = sprintf('TXN-%s-%08d', $date, $transaction->id);

                DB::table('payment_transactions')
                    ->where('id', $transaction->id)
                    ->update(['transaction_id' => $reference]);
            });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function ($table) {
            $table->dropUnique(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
};
