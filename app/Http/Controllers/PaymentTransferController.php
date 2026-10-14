<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentTransferRequest;
use App\Models\PaymentAccount;
use App\Models\PaymentTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentTransferController extends Controller
{
    public function store(StorePaymentTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $accounts = PaymentAccount::whereIn('id', [
                $data['from_payment_account_id'],
                $data['to_payment_account_id'],
            ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $fromAccount = $accounts->get($data['from_payment_account_id']);
            $toAccount = $accounts->get($data['to_payment_account_id']);

            if ($fromAccount === null || $toAccount === null) {
                abort(404);
            }

            if ($fromAccount->status !== 'active' || $toAccount->status !== 'active') {
                throw ValidationException::withMessages([
                    'from_payment_account_id' => 'Both accounts must be active to transfer funds.',
                ]);
            }

            $amountInMinorUnits = PaymentAccount::toMinorUnits($data['amount']);
            $availableBalance = $fromAccount->calculatedBalanceInMinorUnits();

            if ($amountInMinorUnits > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'Insufficient balance in the source account. Available balance: Rs %s.',
                        number_format($availableBalance / 100, 2)
                    ),
                ]);
            }

            PaymentTransfer::create([
                'from_payment_account_id' => $fromAccount->id,
                'to_payment_account_id' => $toAccount->id,
                'amount' => $data['amount'],
                'transfer_date' => now(),
                'note' => $data['note'] ?? null,
            ]);

            $fromAccount->refreshBalance();
            $toAccount->refreshBalance();
        });

        return redirect()->route('payments.index')->with('success', 'Funds transferred successfully.');
    }
}
