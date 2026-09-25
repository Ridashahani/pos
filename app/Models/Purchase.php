<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_no',
        'supplier_id',
        'branch_id',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'created_by',
    ];

    protected $casts = ['purchase_date' => 'date'];

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
