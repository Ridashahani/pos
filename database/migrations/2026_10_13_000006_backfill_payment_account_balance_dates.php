<?php

use App\Models\PaymentAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        PaymentAccount::query()
            ->whereNull('balance_date')
            ->chunkById(100, function ($accounts): void {
                foreach ($accounts as $account) {
                    $latestDailyBalance = $account->dailyBalances()
                        ->orderByDesc('balance_date')
                        ->first();

                    if ($latestDailyBalance !== null) {
                        DB::table('payment_accounts')
                            ->where('id', $account->id)
                            ->update(['balance_date' => $latestDailyBalance->balance_date->toDateString()]);
                    }
                }
            });
    }

    public function down(): void
    {
        // This migration only repairs rows backfilled before balance_date was mass assignable.
    }
};
