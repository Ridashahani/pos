<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SaleDetails;
use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockController extends Controller
{
    private array $stockIn = [
        ['date' => '15 Sep 2026', 'reference' => 'SIN-1001', 'product' => 'Wireless Barcode Scanner', 'quantity' => 25, 'unit_cost' => 65.00, 'supplier' => 'Tech Supplies'],
        ['date' => '13 Sep 2026', 'reference' => 'SIN-1000', 'product' => 'Thermal Receipt Paper', 'quantity' => 100, 'unit_cost' => 3.50, 'supplier' => 'Office Mart'],
        ['date' => '10 Sep 2026', 'reference' => 'SIN-0999', 'product' => 'Cash Drawer', 'quantity' => 8, 'unit_cost' => 85.00, 'supplier' => 'Retail Equip'],
        ['date' => '08 Sep 2026', 'reference' => 'SIN-0998', 'product' => 'USB-C Charging Cable', 'quantity' => 50, 'unit_cost' => 7.25, 'supplier' => 'Tech Supplies'],
    ];

    public function in(Request $request)
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->string('search') . '%');
            })
            ->orderBy('id')
            ->paginate(10);
        $products->appends($request->query());

        $rows = collect($products->items())->map(function (Product $product): array {
            return [
                'product_id' => $product->id,
                'image' => $product->image,
                'date' => $product->buying_date ?: $product->created_at->format('d M Y'),
                'reference' => $product->code,
                'product' => $product->name,
                'brand' => $product->brand,
                'model' => $product->model,
                'imei' => $product->imei,
                'quantity' => $product->stock,
                'unit_cost' => $product->buying_price,
                'currency' => $product->currency ?: 'PKR',
                'category' => $product->category->name,
            ];
        })->all();

        return view('stock.index', array_merge($this->pageData('Stock-In', 'stock-in', $rows), [
            'pagination' => $products,
            'total_products' => $products->total(),
        ]));
    }

    public function inDetails(Product $product)
    {
        return view('stock.details', [
            'product' => $product->load('category'),
        ]);
    }

    public function out(Request $request)
    {
        $sales = SaleDetails::query()
            ->whereHas('sale', fn ($query) => $query->where('sale_status', 'complete'))
            ->with(['product', 'sale'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->get()
            ->map(function (SaleDetails $detail): array {
            $sale = $detail->sale;
            return [
                'date_sort' => $detail->order->order_date,
                'date' => $detail->order->order_date->format('d M Y'),
                'reference' => $detail->order->invoice_no,
                'date' => $sale->sale_date->format('d M Y'),
                'reference' => $sale->invoice_no,
                'product' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_buying_price' => $detail->product->buying_price,
                'unit_price' => $detail->unit_price,
                'net_sold_price' => $detail->total,
                'currency' => $detail->currency ?: ($detail->product->currency ?: 'PKR'),
                'destination' => 'POS Counter',
            ];
        });

        $transfers = StockTransfer::with(['product', 'toBranch'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->get()
            ->map(function (StockTransfer $transfer): array {
                return [
                    'date_sort' => $transfer->created_at,
                    'date' => $transfer->created_at->format('d M Y'),
                    'reference' => $transfer->reference,
                    'product' => $transfer->product->name,
                    'quantity' => $transfer->quantity,
                    'unit_buying_price' => $transfer->product->buying_price,
                    'unit_price' => 0,
                    'net_sold_price' => 0,
                    'currency' => $transfer->product->currency ?: 'PKR',
                    'destination' => 'Transferred to ' . $transfer->toBranch->name,
                ];
            });

        $allRows = $sales->concat($transfers)->sortByDesc('date_sort')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $sales = new LengthAwarePaginator(
            $allRows->forPage($page, 10)->values(),
            $allRows->count(),
            10,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $sales->appends($request->query());
        $rows = $sales->items();

        return view('stock.index', array_merge($this->pageData('Stock-out', 'stock-out', $rows), [
            'pagination' => $sales,
            'total_products' => $sales->total(),
        ]));
    }

    public function transfer()
    {
        $branches = Branch::where('status', 'Active')->orderBy('name')->get()->values();
        $savedRows = StockTransfer::with(['product', 'fromBranch', 'toBranch'])
            ->latest()
            ->get()
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

        return view('stock.index', $this->pageData('Stock-Transfer', 'stock-transfer', $savedRows) + [
            'branches' => $branches,
        ]);
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
        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $product->stock) {
            return back()->withInput()->withErrors([
                'quantity' => "Only {$product->stock} units of this product are available in stock.",
            ]);
        }

        DB::transaction(function () use ($validated, $product): void {
            $product->decrement('stock', $validated['quantity']);

            StockTransfer::create([
                ...$validated,
                'reference' => 'TRF-' . Str::upper(Str::random(6)),
                'status' => 'In Transit',
            ]);
        });

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
            'pending' => $type === 'stock-transfer' ? 2 : 0,
        ];
    }
}
