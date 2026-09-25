<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (PurchaseItem $item): void {
            $item->loadMissing(['product']);

            StockIn::create([
                'purchase_id' => $item->purchase_id,
                'product_id' => $item->product_id,
                'imei' => $item->product?->imei,
                'condition' => 'new',
                'quantity' => $item->quantity,
                'remaining_qty' => $item->quantity,
                'cost_price' => $item->unit_cost,
                'sale_price' => $item->product?->selling_price ?? 0,
            ]);
        });
    }

    protected $fillable = [
        'purchase_id',
        'product_id',
        'variant',
        'quantity',
        'unit_cost',
        'total_amount',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}