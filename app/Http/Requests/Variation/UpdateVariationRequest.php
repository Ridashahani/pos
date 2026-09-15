<?php

namespace App\Http\Requests\Variation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:variations,name,' . $this->route('variation')->id,
            'types' => 'required|array|min:1',
            'types.*' => 'required|string|max:100',
        ];
    }
}