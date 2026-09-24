<?php

namespace App\Http\Requests\Subcategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255|unique:subcategories,name',
            'slug' => 'required|alpha_dash|max:255|unique:subcategories,slug',
            'description' => 'nullable|string',
        ];
    }
}
