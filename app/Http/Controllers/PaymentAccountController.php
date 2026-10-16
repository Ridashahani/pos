<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentAccountRequest;
use App\Http\Requests\StorePaymentAccountDailyBalanceRequest;
use App\Http\Requests\UpdatePaymentAccountRequest;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentAccountController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $accountSummaries = PaymentAccount::withCount('transactions')
            ->withSum('transactions as total_used', 'amount')
            ->withSum('outgoingTransfers as total_transferred_out', 'amount')
            ->withSum('incomingTransfers as total_transferred_in', 'amount')
            ->orderBy('name')
            ->get();
        $accountSummaries->each(function (PaymentAccount $account) use ($today): void {
            $account->setAttribute('today_opening_balance', $account->dailyOpeningBalanceForDate($today) / 100);
            $account->setAttribute('available_balance', $account->calculatedBalanceInMinorUnits(null, $today) / 100);
        });
        $accountTypeStats = collect(['bank', 'jazzcash', 'easypaisa'])->mapWithKeys(
            function (string $type) use ($accountSummaries): array {
                $typeAccounts = $accountSummaries->where('type', $type);
                $totalUsed = $typeAccounts->sum(fn (PaymentAccount $account) => (float) ($account->total_used ?? 0));
                $transferredOut = $typeAccounts->sum(fn (PaymentAccount $account) => (float) ($account->total_transferred_out ?? 0));
                $transferredIn = $typeAccounts->sum(fn (PaymentAccount $account) => (float) ($account->total_transferred_in ?? 0));

                return [$type => [
                    'accounts_count' => $typeAccounts->count(),
                    'previous_balance' => $typeAccounts->sum('previous_balance'),
                    'opening_balance' => $typeAccounts->sum('today_opening_balance'),
                    'current_balance' => $typeAccounts->sum('available_balance'),
                    'transactions_count' => $typeAccounts->sum('transactions_count'),
                    'total_transaction_amount' => $totalUsed,
                    'total_transferred_out' => $transferredOut,
                    'total_transferred_in' => $transferredIn,
                ]];
            }
        );
        $salesReceived = Sale::sum('paid_amount');
        $purchasesPaid = Purchase::sum('paid_amount');
        $accounts = PaymentAccount::withCount('transactions')
            ->orderBy('name')
            ->paginate(10);
        $accounts->getCollection()->each(function (PaymentAccount $account) use ($today): void {
            $todayBalance = $account->dailyBalances()->whereDate('balance_date', $today)->first();
            $account->setAttribute('today_opening_record', $todayBalance);
            $account->setAttribute(
                'today_opening_balance',
                $account->dailyOpeningBalanceForDate($today) / 100
            );
            $account->setAttribute(
                'available_balance',
                $account->calculatedBalanceInMinorUnits(null, $today) / 100
            );
            $account->setAttribute('opening_balance_addition', $account->openingBalanceSuggestionForDate($today) / 100);
        });

        return view('payment-accounts.index', compact(
            'accounts',
            'accountSummaries',
            'accountTypeStats',
            'salesReceived',
            'purchasesPaid'
        ));
    }

    public function create(): View
    {
        return view('payment-accounts.create');
    }

    public function store(StorePaymentAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['current_balance'] = 0;
        PaymentAccount::create($data);

        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account created successfully.');
    }

    public function storeDailyOpeningBalance(
        StorePaymentAccountDailyBalanceRequest $request,
        PaymentAccount $paymentAccount
    ): RedirectResponse {
        $openingBalance = $request->validated('opening_balance');
        $today = now()->toDateString();

        DB::transaction(function () use ($paymentAccount, $openingBalance, $today): void {
            $account = PaymentAccount::whereKey($paymentAccount->id)->lockForUpdate()->firstOrFail();

            if ($account->balance_date?->toDateString() !== $today) {
                $previousDate = now()->subDay()->toDateString();
                $previousBalance = $account->calculatedBalanceInMinorUnits(null, $previousDate);

                $account->update([
                    'previous_balance' => PaymentAccount::formatMinorUnits($previousBalance),
                    'current_balance' => $openingBalance,
                    'balance_date' => $today,
                ]);
            } else {
                $account->update(['current_balance' => $openingBalance]);
            }

            $account->refreshBalance();
        });

        return redirect()->route('payment-accounts.index')
            ->with('success', 'Today’s opening balance saved successfully.');
    }

    public function edit(PaymentAccount $paymentAccount): View
    {
        return view('payment-accounts.edit', compact('paymentAccount'));
    }

    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $data = $request->validated();
        $paymentAccount->update($data);
        $paymentAccount->refreshBalance();

        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account updated successfully.');
    }

    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        $message = $this->deleteOrDeactivate($paymentAccount);

        return redirect()->route('payment-accounts.index')->with('success', $message);
    }

    private function deleteOrDeactivate(PaymentAccount $paymentAccount): string
    {
        if (
            $paymentAccount->transactions()->exists()
            || $paymentAccount->outgoingTransfers()->exists()
            || $paymentAccount->incomingTransfers()->exists()
            || $paymentAccount->dailyBalances()->exists()
        ) {
            $paymentAccount->update(['status' => 'inactive']);
            $message = 'Account has transactions, so it was deactivated instead of deleted.';
        } else {
            $paymentAccount->delete();
            $message = 'Payment account deleted successfully.';
        }

        return $message;
    }
}
