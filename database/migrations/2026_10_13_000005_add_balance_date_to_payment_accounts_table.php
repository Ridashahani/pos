<?php

use App\Models\PaymentAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payment_accounts', 'balance_date')) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->date('balance_date')->nullable()->after('previous_balance');
            });
        }

        PaymentAccount::query()
            ->with(['dailyBalances' => fn ($query) => $query->orderByDesc('balance_date')])
            ->chunkById(100, function ($accounts): void {
                foreach ($accounts as $account) {
                    $latestDailyBalance = $account->dailyBalances->first();

                    if ($latestDailyBalance === null) {
                        continue;
                    }

                    $previousBalance = $account->calculatedBalanceInMinorUnits(
                        null,
                        $latestDailyBalance->balance_date->copy()->subDay()->toDateString()
                    );

                    $account->update([
                        'previous_balance' => PaymentAccount::formatMinorUnits($previousBalance),
                        'opening_balance' => $latestDailyBalance->opening_balance,
                        'balance_date' => $latestDailyBalance->balance_date->toDateString(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_accounts', 'balance_date')) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->dropColumn('balance_date');
            });
        }
    }
};
