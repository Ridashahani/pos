<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTransactionRequest;
use App\Http\Requests\UpdatePaymentTransactionRequest;
use App\Models\PaymentAccount;
use App\Models\PaymentTransaction;
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
        $accounts = PaymentAccount::orderBy('name')->get();
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

        return view('payments.index', compact('accounts', 'transactions', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = PaymentAccount::where('status', 'active')->orderBy('name')->get();

        return view('payments.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentTransactionRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $account = PaymentAccount::whereKey($data['payment_account_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureSufficientBalance($account, $data['amount']);
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

        return view('payments.edit', compact('payment', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentTransactionRequest $request, PaymentTransaction $payment)
    {
        $data = $request->validated();

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

            $excludeTransactionId = $lockedPayment->payment_account_id == $account->id
                ? $lockedPayment->id
                : null;

            $this->ensureSufficientBalance($account, $data['amount'], $excludeTransactionId);
            $lockedPayment->update($data);

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

            $lockedPayment->delete();
            $this->refreshAccountBalance($account);
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction deleted successfully.');
    }

    private function ensureSufficientBalance(
        PaymentAccount $account,
        string|int|float $amount,
        ?int $excludeTransactionId = null
    ): void {
        $transactions = $account->transactions();

        if ($excludeTransactionId !== null) {
            $transactions->where('id', '!=', $excludeTransactionId);
        }

        $availableBalance = $this->toMinorUnits($account->opening_balance)
            - $this->toMinorUnits($transactions->sum('amount'));

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
        $used = $this->toMinorUnits($account->transactions()->sum('amount'));
        $balance = $this->toMinorUnits($account->opening_balance) - $used;
        $account->update([
            'balance' => sprintf('%s%d.%02d', $balance < 0 ? '-' : '', intdiv(abs($balance), 100), abs($balance) % 100),
        ]);
    }

    private function toMinorUnits(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
