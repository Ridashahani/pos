<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentAccountRequest;
use App\Http\Requests\UpdatePaymentAccountRequest;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentAccountController extends Controller
{
    public function index(): View
    {
        $accounts = PaymentAccount::withCount('transactions')
            ->orderBy('name')
            ->paginate(10);

        return view('payment-accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('payment-accounts.create');
    }

    public function store(StorePaymentAccountRequest $request): RedirectResponse
    {
        PaymentAccount::create($request->validated() + [
            'balance' => $request->validated('opening_balance'),
        ]);

        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account created successfully.');
    }

    public function edit(PaymentAccount $paymentAccount): View
    {
        return view('payment-accounts.edit', compact('paymentAccount'));
    }

    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $data = $request->validated();
        $data['balance'] = $paymentAccount->opening_balance - $paymentAccount->transactions()->sum('amount');
        $paymentAccount->update($data);

        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account updated successfully.');
    }

    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        if ($paymentAccount->transactions()->exists()) {
            $paymentAccount->update(['status' => 'inactive']);
            $message = 'Account has transactions, so it was deactivated instead of deleted.';
        } else {
            $paymentAccount->delete();
            $message = 'Payment account deleted successfully.';
        }

        return redirect()->route('payment-accounts.index')->with('success', $message);
    }
}
