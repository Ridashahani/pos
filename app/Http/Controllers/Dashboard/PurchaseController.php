<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index', ['purchases' => Purchase::with(['supplier', 'items'])->latest('purchase_date')->get()]);
    }

    public function create()
    {
        $products = Product::query()
            ->select(['id', 'name', 'variation_types', 'buying_price'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $types = is_array($product->variation_types) ? array_values(array_filter($product->variation_types)) : [];

                $variants = count($types)
                    ? collect($types)->map(fn($type, $index) => [
                        'id' => $product->id . '-' . $index,
                        'name' => $type,
                        'purchasePrice' => (float) $product->buying_price,
                    ])->values()->all()
                    : [[
                        'id' => $product->id . '-0',
                        'name' => 'Standard',
                        'purchasePrice' => (float) $product->buying_price,
                    ]];

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variants' => $variants,
                ];
            });

        return view('purchases.create', [
            'products' => $products,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'branches' => Branch::where('status', 'Active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'purchase_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:Paid,Partial,Due'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated): void {
            $total = collect($validated['items'])->sum(fn (array $item): float => $item['quantity'] * $item['unit_cost']);
            $amountPaid = min((float) ($validated['amount_paid'] ?? 0), $total);
            $purchase = Purchase::create([
                'purchase_number' => 'PUR-' . Str::upper(Str::random(8)),
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $total,
                'amount_paid' => $amountPaid,
                'payment_status' => $validated['payment_status'],
            ]);

            foreach ($validated['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'variant' => $item['variant'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_amount' => $item['quantity'] * $item['unit_cost'],
                ]);

                Product::whereKey($item['product_id'])->lockForUpdate()->increment('stock', $item['quantity']);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase saved and added to stock-in.');
    }

    public function returns()
    {
        return view('purchases.returns', ['purchases' => $this->purchaseRecords()]);
    }

    public function returnCreate(string $purchaseNo)
    {
        $purchase = collect($this->purchaseRecords())->firstWhere('number', $purchaseNo);

        if (!$purchase) {
            $savedPurchase = Purchase::with(['supplier', 'items'])->where('purchase_number', $purchaseNo)->first();
            $purchase = $savedPurchase ? [
                'number' => $savedPurchase->purchase_number,
                'supplier' => $savedPurchase->supplier?->name ?: 'N/A',
                'date' => $savedPurchase->purchase_date->format('Y-m-d'),
                'items' => $savedPurchase->items->sum('quantity'),
            ] : null;
        }

        abort_if(!$purchase, 404);

        return view('purchases.return-create', ['purchase' => $purchase]);
    }

    private function purchaseRecords(): array
    {
        return [
            ['number' => 'PUR-1001', 'supplier' => 'Usman Mobile Traders', 'date' => '2026-09-12', 'items' => 86, 'total' => 'PKR 4,280.00', 'payment' => 'Bank Transfer', 'status' => 'Paid', 'reason' => 'Damaged charger boxes received'],
            ['number' => 'PUR-1002', 'supplier' => 'Al-Madina Mobile Accessories', 'date' => '2026-09-10', 'items' => 124, 'total' => 'PKR 7,650.00', 'payment' => 'Cash', 'status' => 'Paid', 'reason' => 'Wrong mobile covers delivered'],
            ['number' => 'PUR-1003', 'supplier' => 'Hassan Electronics Wholesale', 'date' => '2026-09-08', 'items' => 72, 'total' => 'PKR 5,500.00', 'payment' => 'Credit', 'status' => 'Pending', 'reason' => 'Screen protectors did not match order'],
            ['number' => 'PUR-1004', 'supplier' => 'Usman Mobile Traders', 'date' => '2026-09-05', 'items' => 55, 'total' => 'PKR 3,920.00', 'payment' => 'Bank Transfer', 'status' => 'Paid', 'reason' => 'Power banks failed quality check'],
            ['number' => 'PUR-1005', 'supplier' => 'Al-Madina Mobile Accessories', 'date' => '2026-09-02', 'items' => 98, 'total' => 'PKR 3,500.00', 'payment' => 'Credit', 'status' => 'Pending', 'reason' => 'Quantity was more than ordered'],
        ];
    }
}
