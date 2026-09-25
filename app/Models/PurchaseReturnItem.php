<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;
    protected $fillable = ['purchase_return_id', 'purchase_item_id', 'product_id', 'quantity', 'unit_price', 'total_amount'];


    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
