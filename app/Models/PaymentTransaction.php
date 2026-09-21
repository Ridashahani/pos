<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_account_id',
        'type',
        'customer_name',
        'customer_phone',
        'recipient_name',
        'amount',
        'commission',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];
    public function scopeSearch($query, $search){
        return $query->when($search, function($query) use ($search){
            $query->where('customer_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('account', function($query) use ($search){
                    $query->where('name', 'like', "%{$search}%");
                });
        });
    }

    public function account()
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }
}
