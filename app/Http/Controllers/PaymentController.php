<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTransactionRequest;
use App\Http\Requests\UpdatePaymentTransactionRequest;
use App\Models\PaymentAccount;
use App\Models\PaymentTransaction;
use App\Models\PaymentTransfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accounts = PaymentAccount::where('status', 'active')->orderBy('name')->get();
        $this->attachAvailableBalances($accounts);
        $today = now()->toDateString();
        $todayTransactions = PaymentTransaction::whereDate('transaction_date', $today);

        $summary = [
            'total_transactions' => (clone $todayTransactions)->count(),
            'total_sent' => (clone $todayTransactions)->where('type', 'send')->sum('amount'),
            'total_withdrawals' => (clone $todayTransactions)->where('type', 'withdrawal')->sum('amount'),
            'total_commission' => (clone $todayTransactions)->sum('commission'),
        ];

        $transactions = PaymentTransaction::with('account')
            ->search($request->search)
            ->latest('transaction_date')
            ->paginate(6);
        $transfers = PaymentTransfer::with(['fromAccount', 'toAccount'])
            ->latest('transfer_date')
            ->paginate(6, ['*'], 'transfers_page');

        return view('payments.index', compact('accounts', 'transactions', 'transfers', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = PaymentAccount::where('status', 'active')->orderBy('name')->get();
        $this->attachAvailableBalances($accounts);

        return view('payments.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentTransactionRequest $request)
    {
        $data = $request->validated();

        if ($data['type'] === 'withdrawal') {
            $data['customer_name'] = null;
            $data['customer_phone'] = null;
            $data['recipient_name'] = null;
            $data['recipient_phone'] = null;
        }

        DB::transaction(function () use ($data) {
            $account = PaymentAccount::whereKey($data['payment_account_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $data['transaction_id'] = $this->generateTransactionId($account);
            $this->ensureSufficientBalance($account, $data['amount']);
            $data['transaction_date'] = now();
            PaymentTransaction::create($data);
            $this->refreshAccountBalance($account);
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentTransaction $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentTransaction $payment)
    {
        $accounts = PaymentAccount::where('status', 'active')
            ->orWhere('id', $payment->payment_account_id)
            ->orderBy('name')
            ->get();
        $this->attachAvailableBalances($accounts);

        return view('payments.edit', compact('payment', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentTransactionRequest $request, PaymentTransaction $payment)
    {
        $data = $request->validated();

        if ($data['type'] === 'withdrawal') {
            $data['customer_name'] = null;
            $data['customer_phone'] = null;
            $data['recipient_name'] = null;
            $data['recipient_phone'] = null;
        }

        DB::transaction(function () use ($data, $payment) {
            $lockedPayment = PaymentTransaction::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();
            $accountIds = collect([
                $lockedPayment->payment_account_id,
                $data['payment_account_id'],
            ])->unique()->sort()->values();
            $accounts = PaymentAccount::whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $account = $accounts->get($data['payment_account_id']);

            if ($account === null) {
                abort(404);
            }

            $expectedIdLength = $this->transactionIdLengthForAccount($account);
            if (strlen((string) $lockedPayment->transaction_id) !== $expectedIdLength) {
                $data['transaction_id'] = $this->generateTransactionId($account);
            }

            $excludeTransactionId = $lockedPayment->payment_account_id == $account->id
                ? $lockedPayment->id
                : null;
            $previousAccountId = $lockedPayment->payment_account_id;
            $previousTransactionDate = $lockedPayment->transaction_date->toDateString();

            $this->ensureSufficientBalance($account, $data['amount'], $excludeTransactionId);
            $data['transaction_date'] = now();
            $lockedPayment->update($data);

            $oldAccount = $accounts->get($previousAccountId);
            if ($oldAccount !== null && $previousTransactionDate !== now()->toDateString()) {
                $oldAccount->refreshDailyClosingForDate($previousTransactionDate);
            }

            foreach ($accounts as $affectedAccount) {
                $this->refreshAccountBalance($affectedAccount);
            }
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentTransaction $payment)
    {
        DB::transaction(function () use ($payment) {
            $lockedPayment = PaymentTransaction::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();
            $account = PaymentAccount::whereKey($lockedPayment->payment_account_id)
                ->lockForUpdate()
                ->firstOrFail();
            $transactionDate = $lockedPayment->transaction_date->toDateString();

            $lockedPayment->delete();
            if ($transactionDate !== now()->toDateString()) {
                $account->refreshDailyClosingForDate($transactionDate);
            }
            $this->refreshAccountBalance($account);
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction deleted successfully.');
    }

    private function ensureSufficientBalance(
        PaymentAccount $account,
        string|int|float $amount,
        ?int $excludeTransactionId = null
    ): void {
        $availableBalance = $account->calculatedBalanceInMinorUnits($excludeTransactionId);

        if ($this->toMinorUnits($amount) > $availableBalance) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Insufficient balance in the selected account. Available balance: Rs %s.',
                    number_format($availableBalance / 100, 2)
                ),
            ]);
        }
    }

    private function refreshAccountBalance(PaymentAccount $account): void
    {
        $account->refreshBalance();
    }

    private function attachAvailableBalances(Collection $accounts): void
    {
        $today = now()->toDateString();

        $accounts->each(function (PaymentAccount $account) use ($today): void {
            $account->setAttribute(
                'available_balance',
                $account->calculatedBalanceInMinorUnits(null, $today) / 100
            );
        });
    }

    private function toMinorUnits(string|int|float $amount): int
    {
        return PaymentAccount::toMinorUnits($amount);
    }

    private function generateTransactionId(PaymentAccount $account): string
    {
        $length = $this->transactionIdLengthForAccount($account);
        $minimum = 10 ** ($length - 1);
        $maximum = (10 ** $length) - 1;

        do {
            $transactionId = 'TXN-' . random_int($minimum, $maximum);
        } while (PaymentTransaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    private function transactionIdLengthForAccount(PaymentAccount $account): int
    {
        return strtolower($account->type) === 'jazzcash' ? 12 : 10;
    }
}
