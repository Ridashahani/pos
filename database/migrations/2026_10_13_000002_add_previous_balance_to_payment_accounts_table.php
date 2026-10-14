<?php

use App\Models\PaymentAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payment_accounts', 'previous_balance')) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->decimal('previous_balance', 15, 2)->default(0)->after('opening_balance');
            });
        }

        $today = now()->toDateString();

        PaymentAccount::query()->each(function (PaymentAccount $account) use ($today): void {
            $account->update([
                'previous_balance' => PaymentAccount::formatMinorUnits(
                    $account->previousBalanceInMinorUnits($today)
                ),
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_accounts', 'previous_balance')) {
            Schema::table('payment_accounts', function (Blueprint $table) {
                $table->dropColumn('previous_balance');
            });
        }
    }
};
