<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'holder_name',
        'opening_balance',
        'balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
