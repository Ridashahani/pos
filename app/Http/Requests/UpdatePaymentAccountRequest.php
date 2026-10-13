<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'account_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('payment_accounts', 'account_number')->ignore($this->route('payment_account')),
            ],
            'holder_name' => 'nullable|string|max:255',
            'opening_balance' => 'required|numeric|decimal:0,2|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }
}
