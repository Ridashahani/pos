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
        'variation_id',
        'variation_details',
        'branch_id',
        'batch_no',
        'quantity',
        'remaining_quantity',
        'cost_price',
    ];

    protected $casts = [
        'cost_price' => 'float',
        'variation_details' => 'array',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function soldItems()
    {
        return $this->hasMany(SoldItem::class);
    }
}
