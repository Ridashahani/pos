<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $table = 'stock_in';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'imei',
        'condition',
        'quantity',
        'remaining_qty',
        'cost_price',
        'sale_price',
    ];

    protected $casts = [
        'cost_price' => 'float',
        'sale_price' => 'float',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function soldItems()
    {
        return $this->hasMany(SoldItem::class);
    }
}
