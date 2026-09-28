<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255',
                Rule::unique('settings', 'key')->ignore($this->route('setting')),
            ],
            'value' => ['required', 'string'],
        ];
    }
}