<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_payment_account_id',
        'to_payment_account_id',
        'amount',
        'transfer_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'datetime',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(PaymentAccount::class, 'from_payment_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(PaymentAccount::class, 'to_payment_account_id');
    }
}
