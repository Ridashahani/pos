<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_account_id' => 'required|exists:payment_accounts,id',
            'type' => 'required|in:send,withdrawal',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'recipient_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'commission' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
