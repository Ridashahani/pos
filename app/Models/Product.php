<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'brand_id',
        'model',
        'condition',
        'imei',
        'slug',
        'code',
        'category_id',
        'subcategory_id',
        'branch_id', 'supplier_id', 'note', 'product_type',
        'variation', 'variation_types', 'variation_type',
        'variation_id', 'variation_ids',
        'stock',
        'cost_price',
        'selling_price',
        'currency',
        'product_cost', 'product_price', 'wholesale_price', 'special_price', 'stock_alert',
        'gst_tax', 'tax_type', 'add_product_quantity',
        'status',
        'image',
        'images',
        'buying_date',
        'expire_date',
    ];

    protected $with = ['category'];

    protected $casts = [
            'status' => 'boolean',
        'variation_types' => 'array',
        'variation_ids' => 'array',
        'images' => 'array',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'product_cost' => 'decimal:2',
        'product_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'gst_tax' => 'decimal:2',
        'buying_date' => 'date:Y-m-d',
        'expire_date' => 'date:Y-m-d',
    ];

    public function getPriceIncludingGstAttribute(): float
    {
        $price = (float) ($this->selling_price ?? 0);
        $taxRate = (float) ($this->gst_tax ?? 0);

        return round($price * (1 + ($taxRate / 100)), 2);
    }

    /** Per-request memo: the same image is resolved many times (dropdown rows + cart rows). */
    private static array $imageUrlCache = [];

    public static function imageUrl(?string $image = null): string
    {
        $key = $image ?? '';

        return self::$imageUrlCache[$key] ??= self::resolveImageUrl($image);
    }

    private static function resolveImageUrl(?string $image): string
    {
        if (empty($image)) {
            return asset('assets/images/product/default.webp');
        }

        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }

        $fileName = basename($image);

        if (file_exists(storage_path('app/public/products/' . $fileName))) {
            return asset('storage/products/' . $fileName);
        }

        if (file_exists(public_path('assets/images/product/' . $fileName))) {
            return asset('assets/images/product/' . $fileName);
        }

        return asset('assets/images/product/default.webp');
    }

    /**
     * In stock and not expired: the products a cashier is allowed to sell.
     */
    public function scopeSellable($query)
    {
        return $query->where('stock', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expire_date')
                    ->orWhere('expire_date', '>=', now()->toDateString());
            });
    }

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function variationDefinition()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $search = trim($filters['search'] ?? '');

        $query->when($search !== '', function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('products.name', 'like', '%' . $search . '%')
                    ->orWhere('products.code', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        });

        $query->when($filters['category_id'] ?? null, function ($query, $categoryId) {
            $query->where('products.category_id', $categoryId);
        });

        return $query;
    }
}
