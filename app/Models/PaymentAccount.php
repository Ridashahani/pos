<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'holder_name',
        'opening_balance',
        'previous_balance',
        'balance_date',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'balance_date' => 'date',
    ];

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(PaymentTransfer::class, 'from_payment_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(PaymentTransfer::class, 'to_payment_account_id');
    }

    public function dailyBalances()
    {
        return $this->hasMany(PaymentAccountDailyBalance::class);
    }

    public function dailyOpeningBalanceForDate(string $date): int
    {
        if ($this->balance_date?->toDateString() === $date) {
            return self::toMinorUnits($this->previous_balance)
                + self::toMinorUnits($this->opening_balance);
        }

        $dailyBalance = $this->dailyBalances()->whereDate('balance_date', $date)->first();
        $previousBalance = $this->previousBalanceInMinorUnits($date);

        return $previousBalance + ($dailyBalance !== null ? self::toMinorUnits($dailyBalance->opening_balance) : 0);
    }

    public function openingBalanceSuggestionForDate(string $date): int
    {
        if ($this->balance_date?->toDateString() === $date) {
            return self::toMinorUnits($this->opening_balance);
        }

        $dailyBalance = $this->dailyBalances()->whereDate('balance_date', $date)->first();

        return $dailyBalance !== null
            ? self::toMinorUnits($dailyBalance->opening_balance)
            : 0;
    }

    public function previousBalanceInMinorUnits(string $date): int
    {
        $previousDate = Carbon::parse($date)->subDay()->toDateString();

        if ($this->balance_date !== null && $date >= $this->balance_date->toDateString()) {
            if ($date === $this->balance_date->toDateString()) {
                return self::toMinorUnits($this->previous_balance);
            }

            return $this->calculatedBalanceInMinorUnits(null, $previousDate);
        }

        return $this->legacyCalculatedBalanceInMinorUnits(null, $previousDate);
    }

    public function calculatedBalanceInMinorUnits(
        ?int $excludeTransactionId = null,
        ?string $date = null
    ): int {
        $date ??= now()->toDateString();
        $balanceDate = $this->balance_date?->toDateString();

        if ($balanceDate !== null && $date >= $balanceDate) {
            return $this->calculateFromOpeningBalance(
                self::toMinorUnits($this->previous_balance) + self::toMinorUnits($this->opening_balance),
                $balanceDate,
                $date,
                $excludeTransactionId
            );
        }

        return $this->legacyCalculatedBalanceInMinorUnits($excludeTransactionId, $date);
    }

    private function legacyCalculatedBalanceInMinorUnits(?int $excludeTransactionId, string $date): int
    {
        $dailyBalance = $this->dailyBalances()
            ->whereDate('balance_date', '<=', $date)
            ->orderByDesc('balance_date')
            ->first();

        if ($dailyBalance !== null) {
            $openingBalance = $this->previousBalanceInMinorUnits($dailyBalance->balance_date->toDateString())
                + self::toMinorUnits($dailyBalance->opening_balance);
            $movementStart = $dailyBalance->balance_date->toDateString();
        } else {
            $openingBalance = self::toMinorUnits($this->opening_balance);
            $movementStart = null;
        }

        return $this->calculateFromOpeningBalance($openingBalance, $movementStart, $date, $excludeTransactionId);
    }

    private function calculateFromOpeningBalance(
        int $openingBalance,
        ?string $movementStart,
        string $date,
        ?int $excludeTransactionId
    ): int {
        $transactions = $this->transactions()->whereDate('transaction_date', '<=', $date);
        $outgoingTransfers = $this->outgoingTransfers()->whereDate('transfer_date', '<=', $date);
        $incomingTransfers = $this->incomingTransfers()->whereDate('transfer_date', '<=', $date);

        if ($movementStart !== null) {
            $transactions->whereDate('transaction_date', '>=', $movementStart);
            $outgoingTransfers->whereDate('transfer_date', '>=', $movementStart);
            $incomingTransfers->whereDate('transfer_date', '>=', $movementStart);
        }

        if ($excludeTransactionId !== null) {
            $transactions->where('id', '!=', $excludeTransactionId);
        }

        return $openingBalance
            - self::toMinorUnits($transactions->sum('amount'))
            - self::toMinorUnits($outgoingTransfers->sum('amount'))
            + self::toMinorUnits($incomingTransfers->sum('amount'));
    }

    public function refreshDailyClosingForDate(string $date): void
    {
        $dailyBalance = $this->dailyBalances()->whereDate('balance_date', $date)->first();

        if ($dailyBalance === null) {
            return;
        }

        $closingBalance = $this->calculatedBalanceInMinorUnits(null, $date);
        $dailyBalance->update([
            'closing_balance' => self::formatMinorUnits($closingBalance),
        ]);
    }

    public function refreshBalance(): void
    {
        $today = now()->toDateString();
        $this->refreshDailyClosingForDate($today);
    }

    public static function toMinorUnits(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    public static function formatMinorUnits(int $amount): string
    {
        return sprintf(
            '%s%d.%02d',
            $amount < 0 ? '-' : '',
            intdiv(abs($amount), 100),
            abs($amount) % 100
        );
    }
}
