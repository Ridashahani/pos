<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'customer_id',
        'sale_date',
        'sale_status',
        'total_products',
        'sub_total',
        'vat',
        'invoice_no',
        'total',
        'payment_type',
        'pay_amount',
        'due_amount',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'sub_total' => 'float',
        'vat' => 'float',
        'total' => 'float',
        'pay_amount' => 'float',
        'due_amount' => 'float',
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
