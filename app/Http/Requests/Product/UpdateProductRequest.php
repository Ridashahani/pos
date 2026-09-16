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
            'status' => $this->input('status', 'received'),
            'product_type' => $this->input('product_type', 'single'),
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
            'code' => 'nullable|string|unique:products,code,' . $product->id . '|max:50',
            'category_id' => 'required|integer|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'barcode_symbology' => 'nullable|string|max:50',
            'product_unit' => 'nullable|string|max:50',
            'sale_unit' => 'nullable|string|max:50',
            'purchase_unit' => 'nullable|string|max:50',
            'quantity_limit' => 'nullable|integer|min:0',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'warehouse' => 'nullable|string|max:100',
            'status' => 'required|string|in:received,pending,ordered',
            'note' => 'nullable|string|max:5000',
            'product_type' => 'required|string|in:single,variation',
            'variation_id' => 'nullable|integer|exists:variations,id',
            'variation_ids' => 'required_if:product_type,variation|array|min:1',
            'variation_ids.*' => 'integer|exists:variations,id',
            'variation' => 'nullable|string|max:100',
            'variation_types' => 'required_if:product_type,variation|array|min:1',
            'variation_types.*' => 'string|max:100',
            'variation_type' => 'nullable|string|max:100',
            'product_cost' => 'nullable|numeric|min:0',
            'product_price' => 'nullable|numeric|min:0',
            'single_product_cost' => 'nullable|numeric|min:0',
            'single_product_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'stock_alert' => 'nullable|integer|min:0',
            'order_tax' => 'nullable|numeric|min:0',
            'tax_type' => 'nullable|string|max:30',
            'add_product_quantity' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'currency' => 'required|string|in:PKR,USD',
            'buying_date' => 'date_format:Y-m-d|nullable',
            'expire_date' => 'date_format:Y-m-d|nullable',
        ];
    }
}
