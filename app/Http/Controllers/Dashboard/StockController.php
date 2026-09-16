<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\StockTransfer;
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

    private array $stockTransfers = [
        ['date' => '15 Sep 2026', 'reference' => 'TRF-3012', 'product' => 'Wireless Barcode Scanner', 'quantity' => 4, 'from_branch_index' => 0, 'to_branch_index' => 1, 'status' => 'In Transit'],
        ['date' => '13 Sep 2026', 'reference' => 'TRF-3011', 'product' => 'Thermal Receipt Paper', 'quantity' => 20, 'from_branch_index' => 1, 'to_branch_index' => 0, 'status' => 'Completed'],
        ['date' => '11 Sep 2026', 'reference' => 'TRF-3010', 'product' => 'USB-C Charging Cable', 'quantity' => 10, 'from_branch_index' => 0, 'to_branch_index' => 2, 'status' => 'Completed'],
        ['date' => '07 Sep 2026', 'reference' => 'TRF-3009', 'product' => 'Cash Drawer', 'quantity' => 2, 'from_branch_index' => 2, 'to_branch_index' => 0, 'status' => 'Pending'],
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
        $sales = OrderDetails::query()
            ->whereHas('order', fn ($query) => $query->where('order_status', 'complete'))
            ->with(['product', 'order'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->where('name', 'like', '%' . $request->string('search') . '%');
                });
            })
            ->latest()
            ->paginate(10);
        $sales->appends($request->query());

        $rows = collect($sales->items())->map(function (OrderDetails $detail): array {
            return [
                'date' => $detail->order->order_date->format('d M Y'),
                'reference' => $detail->order->invoice_no,
                'product' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_buying_price' => $detail->product->buying_price,
                'unit_price' => $detail->unit_price,
                'net_sold_price' => $detail->total,
                'currency' => $detail->currency ?: ($detail->product->currency ?: 'PKR'),
                'destination' => 'POS Counter',
            ];
        })->all();

        return view('stock.index', array_merge($this->pageData('Stock-out', 'stock-out', $rows), [
            'pagination' => $sales,
            'total_products' => $sales->total(),
        ]));
    }

    public function transfer()
    {
        $branches = Branch::where('status', 'Active')->orderBy('name')->get()->values();
        $sampleRows = array_map(function (array $row) use ($branches): array {
            $row['from_branch_id'] = $branches->get($row['from_branch_index'])?->id;
            $row['to_branch_id'] = $branches->get($row['to_branch_index'])?->id;
            $row['from'] = $branches->get($row['from_branch_index'])?->name ?? 'No branch assigned';
            $row['to'] = $branches->get($row['to_branch_index'])?->name ?? 'No branch assigned';
            unset($row['from_branch_index'], $row['to_branch_index']);

            return $row;
        }, $this->stockTransfers);
        $savedRows = StockTransfer::with(['product', 'fromBranch', 'toBranch'])
            ->latest()
            ->get()
            ->map(fn (StockTransfer $transfer): array => [
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

        return view('stock.index', $this->pageData('Stock-Transfer', 'stock-transfer', array_merge($savedRows, $sampleRows)) + [
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

        StockTransfer::create([
            ...$validated,
            'reference' => 'TRF-' . Str::upper(Str::random(6)),
            'status' => 'In Transit',
        ]);

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer added successfully.');
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
