<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccountDailyBalance extends Model
{
    protected $fillable = [
        'payment_account_id',
        'balance_date',
        'opening_balance',
        'closing_balance',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }
}
