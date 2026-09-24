<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_type' => $this->input('product_type', 'single'),
            'currency' => $this->input('currency', 'PKR'),
            'buying_price' => $this->input('buying_price', 0),
            'selling_price' => $this->input('selling_price', 0),
            'stock' => $this->input('stock', 0),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'image' => 'image|file|max:1024|nullable',
            'images' => 'nullable|array',
            'images.*' => 'image|file|max:1024',
            'name' => 'required|string|unique:products,name,' . $product->id,
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:100',
            'mobile_type' => 'nullable|string|in:new,used',
            'code' => 'nullable|string|unique:products,code,' . $product->id . '|max:50',
            'category_id' => 'nullable|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:subcategories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'note' => 'nullable|string|max:5000',
            'product_type' => 'nullable|string|max:50',
            'buying_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|in:PKR,USD',
            'buying_date' => 'date_format:Y-m-d|nullable',
        ];
    }
}
