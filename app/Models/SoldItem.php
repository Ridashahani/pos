<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'stock_in_id',
        'product_id',
        'imei',
        'quantity',
        'cost_price',
        'sale_price',
        'discount',
        'profit',
        'customer_id',
        'sold_by',
        'payment_method',
        'warranty_expiry',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'float',
        'sale_price' => 'float',
        'discount' => 'float',
        'profit' => 'float',
        'warranty_expiry' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'sold_by');
    }
}
