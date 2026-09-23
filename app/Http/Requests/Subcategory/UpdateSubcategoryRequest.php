<?php

namespace App\Http\Requests\Subcategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subcategory = $this->route('subcategory');

        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255|unique:subcategories,name,' . $subcategory->id,
            'slug' => 'required|alpha_dash|max:255|unique:subcategories,slug,' . $subcategory->id,
            'description' => 'nullable|string',
        ];
    }
}
