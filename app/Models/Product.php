<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'category_id',
        'brand', 'barcode_symbology', 'product_unit', 'sale_unit', 'purchase_unit',
        'quantity_limit', 'supplier_id', 'warehouse', 'status', 'note', 'product_type',
        'variation', 'variation_types', 'variation_type',
        'variation_id', 'variation_ids',
        'stock',
        'buying_price',
        'selling_price',
        'product_cost', 'product_price', 'wholesale_price', 'special_price', 'stock_alert',
        'order_tax', 'tax_type', 'add_product_quantity',
        'image',
        'images',
        'buying_date',
        'expire_date',
    ];

    protected $with = ['category'];

    protected $casts = [
        'variation_types' => 'array',
        'variation_ids' => 'array',
        'images' => 'array',
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'product_cost' => 'decimal:2',
        'product_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'order_tax' => 'decimal:2',
        'buying_date' => 'date:Y-m-d',
        'expire_date' => 'date:Y-m-d',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variationDefinition()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        });
    }
}
