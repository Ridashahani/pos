<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleDetails;
use App\Models\SoldItem;
use App\Models\StockIn;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function sell(Sale $sale, SaleDetails $detail): void
    {
        $remaining = (int) $detail->quantity;
        $discountPerUnit = $remaining > 0 ? (float) $detail->discount / $remaining : 0;

        $stockRows = StockIn::where('product_id', $detail->product_id)
            ->where('remaining_qty', '>', 0)
            ->when($detail->product->imei, fn ($query, $imei) => $query->where('imei', $imei))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($stockRows->sum('remaining_qty') < $remaining) {
            throw ValidationException::withMessages([
                'stock' => "Insufficient stock for {$detail->product->name}.",
            ]);
        }

        foreach ($stockRows as $stock) {
            if ($remaining === 0) {
                break;
            }

            $quantity = min($remaining, (int) $stock->remaining_qty);
            $stock->decrement('remaining_qty', $quantity);
            $discount = $discountPerUnit * $quantity;
            $salePrice = (float) $detail->unit_price;

            SoldItem::create([
                'sale_id' => $sale->id,
                'stock_in_id' => $stock->id,
                'product_id' => $detail->product_id,
                'imei' => $stock->imei,
                'quantity' => $quantity,
                'cost_price' => $stock->cost_price,
                'sale_price' => $salePrice,
                'discount' => $discount,
                'profit' => (($salePrice - (float) $stock->cost_price) * $quantity) - $discount,
                'customer_id' => $sale->customer_id,
                'sold_by' => auth()->id(),
                'payment_method' => $sale->payment_type,
                'status' => 'sold',
            ]);

            $remaining -= $quantity;
        }
    }
}
