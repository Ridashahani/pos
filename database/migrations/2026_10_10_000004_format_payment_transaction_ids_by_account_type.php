<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_transactions as transactions')
            ->join('payment_accounts as accounts', 'accounts.id', '=', 'transactions.payment_account_id')
            ->select(['transactions.id', 'accounts.type'])
            ->orderBy('transactions.id')
            ->get()
            ->each(function (object $transaction): void {
                $length = strtolower($transaction->type) === 'jazzcash' ? 12 : 10;
                $minimum = 10 ** ($length - 1);
                $maximum = (10 ** $length) - 1;

                do {
                    $transactionId = (string) random_int($minimum, $maximum);
                } while (DB::table('payment_transactions')
                    ->where('transaction_id', $transactionId)
                    ->where('id', '!=', $transaction->id)
                    ->exists());

                DB::table('payment_transactions')
                    ->where('id', $transaction->id)
                    ->update(['transaction_id' => $transactionId]);
            });
    }

    public function down(): void
    {
        // Existing transaction IDs cannot be restored to their previous values.
    }
};
