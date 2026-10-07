<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferAllocation extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'source_stock_in_id',
        'destination_stock_in_id',
        'quantity',
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
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
