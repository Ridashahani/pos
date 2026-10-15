<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    public function rules(): array
    {
        return [
            'return_date'        => ['required', 'date'],
            'reason'             => ['required', 'string', 'max:1000'],
            'status'             => ['nullable', 'in:pending,approved'],
            'return_qty'         => ['required', 'array'],
            'return_qty.*'       => ['nullable', 'integer', 'min:0'],
        ];
    }
}
