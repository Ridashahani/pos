<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with('purchase.supplier')
            ->withSum('items as returned_qty', 'quantity');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                    ->orWhereHas('purchase', fn($p) => $p->where('purchase_no', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }



        $returns = $query->latest()->paginate(15)->withQueryString();

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
                        "return_qty.{$item->id}" => "Return quantity cannot be greater than the available quantity ({$available}).",
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
                    'return_qty' => 'Please enter the return quantity for at least one item.',
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

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('purchase.supplier', 'items.product');

        return view('purchases.return-show', compact('purchaseReturn'));
    }
}
