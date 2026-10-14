<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $activeAccount = fn () => Rule::exists('payment_accounts', 'id')->where(
            fn ($query) => $query->where('status', 'active')
        );

        return [
            'from_payment_account_id' => ['required', 'integer', $activeAccount()],
            'to_payment_account_id' => [
                'required',
                'integer',
                'different:from_payment_account_id',
                $activeAccount(),
            ],
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'from_payment_account_id.exists' => 'Select an active source account.',
            'to_payment_account_id.exists' => 'Select an active destination account.',
            'to_payment_account_id.different' => 'Source and destination accounts must be different.',
        ];
    }
}
