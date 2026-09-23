<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetails extends Model
{
    use HasFactory;

    protected $table = 'sale_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'currency',
        'discount',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

}
