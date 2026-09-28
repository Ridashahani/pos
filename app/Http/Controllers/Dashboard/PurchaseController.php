<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'branch', 'items.product'])
            ->when($request->filled('search') && $request->filled('search_by'), function ($q) use ($request) {
                $search = $request->search;
                $searchBy = $request->search_by;

                match ($searchBy) {
                    'purchase_no' => $q->where('purchase_no', 'like', "%{$search}%"),
                    'product' => $q->whereHas('items.product', fn($q2) => $q2->where('name', 'like', "%{$search}%")),
                    'supplier' => $q->whereHas('supplier', fn($q2) => $q2->where('name', 'like', "%{$search}%")),
                    'branch' => $q->whereHas('branch', fn($q2) => $q2->where('name', 'like', "%{$search}%")),
                    default => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalPurchases = Purchase::sum('total_amount');
        $itemsReceived = PurchaseItem::sum('quantity');
        $pendingPayments = Purchase::sum('due_amount');

        return view('purchases.index', compact('purchases', 'totalPurchases', 'itemsReceived', 'pendingPayments'));
    }

    public function create()
    {
        return view('purchases.create', [
            'products' => Product::select(['id', 'name', 'buying_price'])->orderBy('name')->get(),
            'variations' => Variation::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        $itemsTotal = collect($validated['items'])->sum(
            fn($item) => $item['quantity'] * $item['unit_price']
        );
        $paidAmount = $validated['paid_amount'] ?? 0;

        DB::transaction(function () use ($validated, $itemsTotal, $paidAmount) {
            $purchase = Purchase::create([
                'purchase_no' => $this->generatePurchaseNo(),
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $itemsTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => max(0, $itemsTotal - $paidAmount),
                'payment_status' => $validated['payment_status'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'variations' => $item['variations'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'branch', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');

        return view('purchases.edit', [
            'purchase' => $purchase,
            'products' => Product::select(['id', 'name', 'buying_price'])->orderBy('name')->get(),
            'variations' => Variation::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function update(StorePurchaseRequest $request, Purchase $purchase)
    {
        $validated = $request->validated();

        $itemsTotal = collect($validated['items'])->sum(
            fn($item) => $item['quantity'] * $item['unit_price']
        );
        $paidAmount = $validated['paid_amount'] ?? 0;

        DB::transaction(function () use ($validated, $purchase, $itemsTotal, $paidAmount) {
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $itemsTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => max(0, $itemsTotal - $paidAmount),
                'payment_status' => $validated['payment_status'],
            ]);

            $purchase->items()->delete();

            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'variations' => $item['variations'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }

    private function generatePurchaseNo(): string
    {
        $lastId = (int) (Purchase::max('id') ?? 0);

        do {
            $lastId++;
            $purchaseNo = 'PUR-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        } while (Purchase::where('purchase_no', $purchaseNo)->exists());

        return $purchaseNo;
    }
}
