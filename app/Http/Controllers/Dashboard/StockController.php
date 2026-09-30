<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\SoldItem;
use App\Models\StockIn;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockController extends Controller
{
   public function in(Request $request)
{
    $stockIns = StockIn::with([
        'product.category',
        'purchase.supplier',
        'branch',
    ])
        ->where('remaining_quantity', '>', 0)
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
            });
        })
        ->latest()
        ->paginate(10);
    $stockIns->appends($request->query());

    $rows = collect($stockIns->items())->map(function (StockIn $stock): array {
        return [
            'id' => $stock->id,
            'product_id' => $stock->product_id,
            'product' => $stock->product->name,
            'currency' => $stock->product->currency ?: 'PKR',
            'variation_id' => $stock->variation_id,
            'branch_id' => $stock->branch_id,
            'branch' => $stock->branch->name,
            'purchase_id' => $stock->purchase_id,
            'purchase' => $stock->purchase?->purchase_no ?: 'Manual',
            'batch_no' => $stock->batch_no,
            'cost_price' => $stock->cost_price,
            'quantity' => $stock->quantity,
            'remaining_quantity' => $stock->remaining_quantity,
            'created_at' => $stock->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $stock->updated_at->format('Y-m-d H:i:s'),
            'stock_in_id' => $stock->id,
        ];
    })->all();

    $thisMonthCount = StockIn::where('remaining_quantity', '>', 0)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->count();

    return view('stock.index', array_merge($this->pageData('Stock-In', 'stock-in', $rows), [
        'pagination' => $stockIns,
        'total_products' => $stockIns->total(),
        'total_units' => StockIn::where('remaining_quantity', '>', 0)->sum('remaining_quantity'),
        'this_month' => $thisMonthCount, // override pageData() ka wrong value
    ]));
}

    public function inDetails(StockIn $stockIn)
    {
        return view('stock.details', [
            'purchaseItem' => $stockIn->load([
                'product.category',
                'product.brand',
                'purchase.supplier',
                'branch',
            ]),
        ]);
    }

    public function out(Request $request)
    {
        return redirect()->route('stock.sold-items', $request->query());
    }

    public function soldItems(Request $request)
    {
        $soldItems = SoldItem::query()
            ->with(['product', 'sale'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->paginate(10);
        $soldItems->appends($request->query());

        $rows = collect($soldItems->items())->map(function (SoldItem $item): array {
            return [
                'date' => $item->created_at->format('d M Y'),
                'sold_item_id' => $item->id,
                'sale_id' => $item->sale_id,
                'reference' => $item->sale->invoice_no,
                'product' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_buying_price' => $item->cost_price,
                'unit_price' => $item->sale_price,
                'net_sold_price' => ($item->sale_price * $item->quantity) - $item->discount,
                'currency' => $item->product->currency ?: 'PKR',
                'destination' => 'POS Counter',
                'status' => $item->status,
            ];
        })->all();

        return view('stock.index', array_merge($this->pageData('Sold Items', 'sold-items', $rows), [
            'pagination' => $soldItems,
            'total_products' => $soldItems->total(),
        ]));
    }

    public function outOfStock(Request $request)
    {
        $stockIns = StockIn::with(['product', 'purchase'])
            ->where('remaining_quantity', '<=', 0)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->paginate(10);
        $stockIns->appends($request->query());

        $rows = collect($stockIns->items())->map(function (StockIn $stock): array {
            return [
                'date' => $stock->purchase?->purchase_date?->format('d M Y') ?: $stock->created_at->format('d M Y'),
                'reference' => $stock->batch_no,
                'product' => $stock->product->name,
                'quantity' => $stock->remaining_quantity,
                'currency' => $stock->product->currency ?: 'PKR',
                'stock_in_id' => $stock->id,
                'product_id' => $stock->product_id,
            ];
        })->all();

        return view('stock.index', array_merge($this->pageData('Out of Stock', 'out-of-stock', $rows), [
            'pagination' => $stockIns,
            'total_products' => $stockIns->total(),
        ]));
    }

    public function transfer(Request $request)
    {
        $branches = Branch::where('status', 'Active')->orderBy('name')->get()->values();
        $transfers = StockTransfer::with(['product', 'fromBranch', 'toBranch'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $savedRows = $transfers->getCollection()
            ->map(fn (StockTransfer $transfer): array => [
                'id' => $transfer->id,
                'date' => $transfer->created_at->format('d M Y'),
                'reference' => $transfer->reference,
                'product' => $transfer->product->name,
                'quantity' => $transfer->quantity,
                'from_branch_id' => $transfer->from_branch_id,
                'to_branch_id' => $transfer->to_branch_id,
                'from' => $transfer->fromBranch->name,
                'to' => $transfer->toBranch->name,
                'status' => $transfer->status,
            ])
            ->all();

        return view('stock.index', array_merge($this->pageData('Stock-Transfer', 'stock-transfer', $savedRows), [
            'branches' => $branches,
            'pagination' => $transfers,
            'total_products' => $transfers->total(),
            'total_units' => StockTransfer::sum('quantity'),
            'pending' => StockTransfer::whereIn('status', ['Pending', 'In Transit'])->count(),
        ]));
    }

    public function createTransfer()
    {
        return view('stock.transfer-create', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
            'branches' => Branch::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function editTransfer(StockTransfer $transfer)
    {
        return view('stock.transfer-create', [
            'transfer' => $transfer,
            'products' => Product::where('stock', '>', 0)
                ->orWhere('id', $transfer->product_id)
                ->orderBy('name')
                ->get(),
            'branches' => Branch::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

public function storeTransfer(Request $request)
{
    $validated = $request->validate([
        'product_id' => ['required', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1'],
        'from_branch_id' => ['required', 'exists:branches,id'],
        'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
    ]);

    $available = DB::transaction(function () use ($validated): ?int {
        $product = Product::whereKey($validated['product_id'])->lockForUpdate()->firstOrFail();

        if ($validated['quantity'] > $product->stock) {
            return $product->stock; // available quantity return kar dein, transaction ko fail hone dein
        }

        $product->decrement('stock', $validated['quantity']);

        StockTransfer::create([
            ...$validated,
            'reference' => 'TRF-' . Str::upper(Str::random(6)),
            'status' => 'In Transit',
        ]);

        return null; // null = success
    });

    if ($available !== null) {
        return back()->withInput()->withErrors([
            'quantity' => "Only {$available} units of this product are available in stock.",
        ]);
    }

    return redirect()->route('stock.transfer')->with('success', 'Stock transfer added successfully.');
}

    public function updateTransfer(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
        ]);
        $available = DB::transaction(function () use ($validated, $transfer): ?int {
            $oldProduct = Product::whereKey($transfer->product_id)->lockForUpdate()->firstOrFail();
            $oldProduct->increment('stock', $transfer->quantity);

            $newProduct = Product::whereKey($validated['product_id'])->lockForUpdate()->firstOrFail();
            if ($validated['quantity'] > $newProduct->stock) {
                $oldProduct->decrement('stock', $transfer->quantity);
                return $newProduct->stock;
            }

            $newProduct->decrement('stock', $validated['quantity']);
            $transfer->update($validated);

            return null;
        });

        if ($available !== null) {
            return back()->withInput()->withErrors([
                'quantity' => "Only {$available} units of this product are available in stock.",
            ]);
        }

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer updated successfully.');
    }

    public function destroyTransfer(StockTransfer $transfer)
    {
        DB::transaction(function () use ($transfer): void {
            Product::whereKey($transfer->product_id)->lockForUpdate()->firstOrFail()->increment('stock', $transfer->quantity);
            $transfer->delete();
        });

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer deleted successfully.');
    }

    public function destroySoldItem(SoldItem $soldItem)
    {
        $soldItem->delete();

        return redirect()->route('stock.sold-items')->with('success', 'Sold item deleted successfully.');
    }

    public function clearSoldItems()
    {
        $deleted = SoldItem::query()->delete();

        return redirect()->route('stock.sold-items')->with('success', "{$deleted} sold item records deleted successfully.");
    }

    public function destroyOutOfStock(StockIn $stockIn)
    {
        if ($stockIn->remaining_quantity > 0) {
            return redirect()->route('stock.out-of-stock')->with('error', 'Only out-of-stock products can be deleted from this page.');
        }

        try {
            $stockIn->delete();
        } catch (\Illuminate\Database\QueryException) {
            return redirect()->route('stock.out-of-stock')->with('error', 'This stock-in record has sold items and cannot be deleted.');
        }

        return redirect()->route('stock.out-of-stock')->with('success', 'Out-of-stock stock-in record deleted successfully.');
    }

    public function clearOutOfStock()
    {
        $deleted = 0;
        $skipped = 0;

        StockIn::query()
            ->where('remaining_quantity', '<=', 0)
            ->get()
            ->each(function (StockIn $stockIn) use (&$deleted, &$skipped): void {
                try {
                    $stockIn->delete();
                    $deleted++;
                } catch (\Illuminate\Database\QueryException) {
                    $skipped++;
                }
            });

        $message = "{$deleted} out-of-stock stock-in records deleted successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} records with sold items were skipped.";
        }

        return redirect()->route('stock.out-of-stock')->with('success', $message);
    }

    public function clearTransfers()
    {
        DB::transaction(function (): void {
            StockTransfer::query()->get()->each(function (StockTransfer $transfer): void {
                Product::whereKey($transfer->product_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stock', $transfer->quantity);
                $transfer->delete();
            });
        });

        return redirect()->route('stock.transfer')->with('success', 'All stock transfer records deleted successfully.');
    }

    private function pageData(string $title, string $type, iterable $rows): array
    {
        $rows = collect($rows);

        return [
            'title' => $title,
            'type' => $type,
            'rows' => $rows,
            'total_products' => count($rows),
            'total_units' => $rows->sum('quantity'),
            'this_month' => count($rows),
            'pending' => $type === 'stock-transfer'
                ? $rows->whereIn('status', ['Pending', 'In Transit'])->count()
                : 0,
        ];
    }

}
