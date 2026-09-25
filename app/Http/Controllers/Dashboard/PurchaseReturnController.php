<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with('purchase.supplier')
            ->withSum('items as returned_qty', 'quantity')
            ->latest()
            ->paginate(15);

        $purchases = Purchase::orderByDesc('id')->get(['id', 'purchase_no']);

        return view('purchases.returns', compact('returns', 'purchases'));
    }

    public function create(Purchase $purchase)
    {
        $purchase->load(['supplier', 'branch', 'items.product']);

        $returned = PurchaseReturnItem::whereIn('purchase_item_id', $purchase->items->pluck('id'))
            ->selectRaw('purchase_item_id, SUM(quantity) as qty')
            ->groupBy('purchase_item_id')
            ->pluck('qty', 'purchase_item_id');

        return view('purchases.return-create', compact('purchase', 'returned'));
    }

    public function store(StorePurchaseReturnRequest $request, Purchase $purchase)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $purchase) {
            $items = $purchase->items()->lockForUpdate()->get();

            $returned = PurchaseReturnItem::whereIn('purchase_item_id', $items->pluck('id'))
                ->selectRaw('purchase_item_id, SUM(quantity) as qty')
                ->groupBy('purchase_item_id')
                ->pluck('qty', 'purchase_item_id');

            $lines = [];
            foreach ($items as $item) {
                $qty = (int) ($data['return_qty'][$item->id] ?? 0);
                if ($qty <= 0) continue;

                $available = $item->quantity - (int) ($returned[$item->id] ?? 0);
                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        "return_qty.{$item->id}" => "Return qty {$available} se zyada nahi ho sakti.",
                    ]);
                }

                $lines[] = [
                    'purchase_item_id' => $item->id,
                    'product_id'       => $item->product_id,
                    'quantity'         => $qty,
                    'unit_price'       => $item->unit_price,
                    'total_amount'     => $qty * $item->unit_price,
                ];
            }

            if (!$lines) {
                throw ValidationException::withMessages([
                    'return_qty' => 'Kam az kam ek item ki return quantity likhein.',
                ]);
            }

            $return = PurchaseReturn::create([
                'return_no'    => 'TMP-' . uniqid(),
                'purchase_id'  => $purchase->id,
                'return_date'  => $data['return_date'],
                'reason'       => $data['reason'],
                'status'       => $data['status'],
                'total_amount' => collect($lines)->sum('total_amount'),
                'created_by'   => auth()->id(),
            ]);

            $return->update(['return_no' => 'RET-' . str_pad($return->id, 4, '0', STR_PAD_LEFT)]);
            $return->items()->createMany($lines);
        });

        return redirect()->route('purchases.returns')->with('success', 'Purchase return saved.');
    }
}
