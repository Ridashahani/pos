<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'source_stock_in_id',
        'destination_stock_in_id',
        'quantity',
    ];

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function sourceStockIn()
    {
        return $this->belongsTo(StockIn::class, 'source_stock_in_id');
    }

    public function destinationStockIn()
    {
        return $this->belongsTo(StockIn::class, 'destination_stock_in_id');
    }
}
