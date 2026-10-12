<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTransactionRequest extends FormRequest
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
            'customer_name' => 'nullable|required_if:type,send|string|max:255',
            'customer_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'commission' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Customer phone may contain numbers only.',
            'recipient_phone.regex' => 'Recipient phone may contain numbers only.',
        ];
    }
}
