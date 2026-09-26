<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = ['return_no', 'purchase_id', 'return_date', 'reason', 'status', 'total_amount', 'created_by'];
    protected $casts = ['return_date' => 'date'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
