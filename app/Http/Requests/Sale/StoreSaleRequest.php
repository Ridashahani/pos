<?php

namespace App\Http\Requests\Sale;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = $this->user();
                    $isAdmin = $user->roles()->whereRaw('LOWER(name) = ?', ['admin'])->exists();
                    $branchQuery = Branch::whereKey($value)->where('status', 'Active');

                    if (!$isAdmin) {
                        $branchQuery->whereHas('users', fn ($query) => $query->whereKey($user->id));
                    }

                    if (!$branchQuery->exists()) {
                        $fail('Select an active branch available to your account.');
                    }
                },
            ],
            'payment_type' => 'required|string',
            'pay_amount' => 'required|numeric|min:0',
        ];
    }
}
