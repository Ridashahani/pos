<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'branch_id',
        'purchase_date',
        'total_amount',
        'amount_paid',
        'payment_status',
    ];

    protected $casts = [
        'purchase_date' => 'date:Y-m-d',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}