<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_transactions')
            ->select(['id', 'transaction_id'])
            ->orderBy('id')
            ->each(function (object $transaction): void {
                $transactionId = (string) $transaction->transaction_id;

                if (! str_starts_with($transactionId, 'TXN-')) {
                    DB::table('payment_transactions')
                        ->where('id', $transaction->id)
                        ->update(['transaction_id' => 'TXN-' . $transactionId]);
                }
            });
    }

    public function down(): void
    {
        DB::table('payment_transactions')
            ->where('transaction_id', 'like', 'TXN-%')
            ->update([
                'transaction_id' => DB::raw("SUBSTRING(transaction_id, 5)"),
            ]);
    }
};
