<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTransactionRequest;
use App\Http\Requests\UpdatePaymentTransactionRequest;
use App\Models\PaymentAccount;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($request) {
            PaymentTransaction::create($request->validated());
            $this->refreshAccountBalances();
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
        DB::transaction(function () use ($request, $payment) {
            $payment->update($request->validated());
            $this->refreshAccountBalances();
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentTransaction $payment)
    {
        DB::transaction(function () use ($payment) {
            $payment->delete();
            $this->refreshAccountBalances();
        });

        return redirect()->route('payments.index')->with('success', 'Payment transaction deleted successfully.');
    }

    private function refreshAccountBalances(): void
    {
        PaymentAccount::query()->each(function (PaymentAccount $account) {
            $used = $account->transactions()->sum('amount');
            $account->update(['balance' => $account->opening_balance - $used]);
        });
    }
}
