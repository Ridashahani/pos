<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'invoice_no',
        'branch_id',
        'customer_id',
        'user_id',
        'sale_date',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'sale_date'   => 'datetime',
        'subtotal'    => 'float',
        'discount'    => 'float',
        'tax'         => 'float',
        'grand_total' => 'float',
        'paid_amount' => 'float',
        'due_amount'  => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetails::class, 'sale_id');
    }

    public function soldItems()
    {
        return $this->hasMany(SoldItem::class);
    }
}
