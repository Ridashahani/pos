<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\SoldItem;
use App\Models\StockIn;
use App\Models\StockTransfer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Picqer\Barcode\BarcodeGeneratorHTML;

class StockController extends Controller
{
    /* =====================================================
     |  HELPERS (baar baar use hone wali cheezein)
     ===================================================== */

    // Product ke naam se search (sab list pages mein same)
    private function searchByProduct($query, Request $request)
    {
        return $query->when($request->filled('search'), function ($q) use ($request) {
            $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%' . $request->search . '%'));
        });
    }

    // Sab list pages ek hi view 'stock.index' kholte hain.
    // $extra mein jo keys dengay woh default values ko override kar dengi.
    private function page(string $title, string $type, $paginator, array $rows, array $extra = [])
    {
        $rows = collect($rows);

        return view('stock.index', array_merge([
            'title'          => $title,
            'type'           => $type,
            'rows'           => $rows,
            'pagination'     => $paginator,
            'total_products' => $paginator->total(),
            'total_units'    => $rows->sum('quantity'),
            'this_month'     => $rows->count(),
            'pending'        => 0,
        ], $extra));
    }

    // Variation ko "Size: L, Color: Red" bana do
    private function variationLabel(StockIn $stockIn): string
    {
        $text = collect($stockIn->variation_details ?? [])
            ->map(fn ($v) => trim(($v['name'] ?? '') . ': ' . ($v['value'] ?? ''), ': '))
            ->filter()
            ->implode(', ');

        return $text ?: ($stockIn->variation?->name ?? '-');
    }

    /* =====================================================
     |  STOCK IN
     ===================================================== */

    // Woh batches jin mein maal bacha hai
    public function in(Request $request)
    {
        $query = StockIn::with(['product.category', 'purchase.supplier', 'branch', 'variation'])
            ->where('remaining_quantity', '>', 0);

        $stockIns = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $stockIns->getCollection()->map(fn (StockIn $s) => [
            'id'                 => $s->id,
            'stock_in_id'        => $s->id,
            'product_id'         => $s->product_id,
            'product'            => $s->product->name,
            'variation'          => $this->variationLabel($s),
            'variation_id'       => $s->variation_id,
            'currency'           => $s->product->currency ?: 'PKR',
            'branch_id'          => $s->branch_id,
            'branch'             => $s->branch->name,
            'purchase_id'        => $s->purchase_id,
            'purchase'           => $s->purchase?->purchase_no ?: 'Manual',
            'batch_no'           => $s->batch_no,
            'cost_price'         => $s->cost_price,
            'quantity'           => $s->quantity,
            'remaining_quantity' => $s->remaining_quantity,
            'created_at'         => $s->created_at->format('Y-m-d H:i:s'),
            'updated_at'         => $s->updated_at->format('Y-m-d H:i:s'),
        ])->all();

        return $this->page('Stock-In', 'stock-in', $stockIns, $rows, [
            'total_units' => StockIn::where('remaining_quantity', '>', 0)->sum('remaining_quantity'),
            'this_month'  => StockIn::where('remaining_quantity', '>', 0)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ]);
    }

    // Ek batch ki detail
    public function inDetails(StockIn $stockIn)
    {
        $stockIn->load([
            'product.category',
            'product.subcategory',
            'product.brand',
            'purchase.supplier',
            'branch',
            'variation',
        ]);

        $barcodeGenerator = new BarcodeGeneratorHTML();
        $barcode = $stockIn->product?->code
            ? $barcodeGenerator->getBarcode($stockIn->product->code, $barcodeGenerator::TYPE_CODE_128)
            : null;

        return view('stock.details', [
            'purchaseItem'   => $stockIn,
            'variationLabel' => $this->variationLabel($stockIn),
            'barcode'        => $barcode,
        ]);
    }

    /* =====================================================
     |  SOLD ITEMS
     ===================================================== */

    public function out(Request $request)
    {
        return redirect()->route('stock.sold-items', $request->query());
    }

    public function soldItems(Request $request)
    {
        $query = SoldItem::with(['product', 'sale']);

        $soldItems = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $soldItems->getCollection()->map(fn (SoldItem $i) => [
            'date'              => $i->created_at->format('d M Y'),
            'sold_item_id'      => $i->id,
            'sale_id'           => $i->sale_id,
            'reference'         => $i->sale->invoice_no,
            'product'           => $i->product->name,
            'quantity'          => $i->quantity,
            'unit_buying_price' => $i->cost_price,
            'unit_price'        => $i->sale_price,
            'net_sold_price'    => ($i->sale_price * $i->quantity) - $i->discount,
            'currency'          => $i->product->currency ?: 'PKR',
            'destination'       => 'POS Counter',
            'status'            => $i->status,
        ])->all();

        return $this->page('Sold Items', 'sold-items', $soldItems, $rows);
    }

    // NOTE: asal code ki tarah yeh stock wapis add nahi karta
    public function destroySoldItem(SoldItem $soldItem)
    {
        $soldItem->delete();

        return redirect()->route('stock.sold-items')->with('success', 'Sold item deleted successfully.');
    }

    public function clearSoldItems()
    {
        $deleted = SoldItem::query()->delete();

        return redirect()->route('stock.sold-items')
            ->with('success', "{$deleted} sold item records deleted successfully.");
    }

    /* =====================================================
     |  OUT OF STOCK
     ===================================================== */

    public function outOfStock(Request $request)
    {
        $query = StockIn::with(['product', 'purchase'])->where('remaining_quantity', '<=', 0);

        $stockIns = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $stockIns->getCollection()->map(fn (StockIn $s) => [
            'date'        => $s->purchase?->purchase_date?->format('d M Y') ?: $s->created_at->format('d M Y'),
            'reference'   => $s->batch_no,
            'product'     => $s->product->name,
            'quantity'    => $s->remaining_quantity,
            'currency'    => $s->product->currency ?: 'PKR',
            'stock_in_id' => $s->id,
            'product_id'  => $s->product_id,
        ])->all();

        return $this->page('Out of Stock', 'out-of-stock', $stockIns, $rows);
    }

    public function destroyOutOfStock(StockIn $stockIn)
    {
        $back = redirect()->route('stock.out-of-stock');

        if ($stockIn->remaining_quantity > 0) {
            return $back->with('error', 'Only out-of-stock products can be deleted from this page.');
        }

        if ($error = $this->deleteStockIn($stockIn)) {
            return $back->with('error', $error);
        }

        return $back->with('success', 'Out-of-stock stock-in record deleted successfully.');
    }

    // Batch delete karo. Kamyab ho to null, fail ho to error ka text wapis deta hai.
    // Fail hone ki asli wajah storage/logs/laravel.log mein likh di jati hai.
    private function deleteStockIn(StockIn $stockIn): ?string
    {
        try {
            $stockIn->delete();

            return null;
        } catch (QueryException $e) {
            report($e);

            return 'This stock-in record is used by sold items or stock transfers and cannot be deleted.';
        }
    }

    public function clearOutOfStock()
    {
        $deleted = 0;
        $skipped = 0;

        foreach (StockIn::where('remaining_quantity', '<=', 0)->get() as $stockIn) {
            if ($this->deleteStockIn($stockIn)) {
                $skipped++; // sold items ya transfers juray hain
            } else {
                $deleted++;
            }
        }

        $back = redirect()->route('stock.out-of-stock');

        // Kuch bhi delete na hua to green "success" dikhana galat hai, error dikhao
        if ($deleted === 0 && $skipped > 0) {
            $recordLabel = $skipped === 1 ? 'record is' : 'records are';
            $relatedLabel = $skipped === 1 ? 'a sale or stock transfer' : 'sales or stock transfers';
            $keptLabel = $skipped === 1 ? 'was kept.' : 'were kept.';

            return $back->with('error', "No records were deleted. {$skipped} out-of-stock {$recordLabel} linked to {$relatedLabel} and {$keptLabel}");
        }

        $message = "{$deleted} out-of-stock stock-in records deleted successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} records used by sold items or stock transfers were skipped.";
        }

        return $back->with('success', $message);
    }

    /* =====================================================
     |  STOCK TRANSFER
     ===================================================== */

    public function transfer(Request $request)
    {
        $query = StockTransfer::with(['product', 'fromBranch', 'toBranch']);

        $transfers = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $transfers->getCollection()->map(fn (StockTransfer $t) => [
            'id'             => $t->id,
            'date'           => $t->created_at->format('d M Y'),
            'reference'      => $t->reference,
            'product'        => $t->product->name,
            'quantity'       => $t->quantity,
            'from_branch_id' => $t->from_branch_id,
            'to_branch_id'   => $t->to_branch_id,
            'from'           => $t->fromBranch->name,
            'to'             => $t->toBranch->name,
            'status'         => $t->status,
        ])->all();

        return $this->page('Stock-Transfer', 'stock-transfer', $transfers, $rows, [
            'branches'    => Branch::where('status', 'Active')->orderBy('name')->get()->values(),
            'total_units' => StockTransfer::sum('quantity'),
            'pending'     => StockTransfer::whereIn('status', ['Pending', 'In Transit'])->count(),
        ]);
    }

    // Create aur Edit dono ka form ek hi jaisa hai
    public function createTransfer()
    {
        return $this->transferForm();
    }

    public function editTransfer(StockTransfer $transfer)
    {
        return $this->transferForm($transfer);
    }

    private function transferForm(?StockTransfer $transfer = null)
    {
        $stockIns = StockIn::with(['product', 'branch', 'variation'])
            ->where(function ($q) use ($transfer) {
                $q->where('remaining_quantity', '>', 0);
                // edit mein purani batch bhi dikhao chahe uska maal 0 ho
                if ($transfer?->stock_in_id) {
                    $q->orWhereKey($transfer->stock_in_id);
                }
            })
            ->orderBy('id')
            ->get();

        return view('stock.transfer-create', [
            'transfer' => $transfer,
            'stockIns' => $stockIns,
            'branches' => Branch::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function storeTransfer(Request $request)
    {
        $data = $this->validateTransfer($request);

        DB::transaction(fn () => $this->applyTransfer($data));

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer added successfully.');
    }

    public function updateTransfer(Request $request, StockTransfer $transfer)
    {
        $data = $this->validateTransfer($request);

        DB::transaction(function () use ($data, $transfer) {
            $transfer = StockTransfer::whereKey($transfer->id)->lockForUpdate()->firstOrFail();

            $this->restoreTransferInventory($transfer); // 1) purana maal wapis
            $this->applyTransfer($data, $transfer);     // 2) naya lagao (checks ke saath)
        });

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer updated successfully.');
    }

    public function destroyTransfer(StockTransfer $transfer)
    {
        $this->removeTransfer($transfer->id);

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer deleted successfully.');
    }

    public function clearTransfers()
    {
        StockTransfer::orderBy('id')->pluck('id')->each(fn ($id) => $this->removeTransfer($id));

        return redirect()->route('stock.transfer')->with('success', 'All stock transfer records deleted successfully.');
    }

    /* ---------- Transfer ke helpers ---------- */

    private function validateTransfer(Request $request): array
    {
        return $request->validate([
            'stock_in_id'  => ['required', 'exists:stock_in,id'],
            'quantity'     => ['required', 'integer', 'min:1'],
            'to_branch_id' => ['required', 'exists:branches,id'],
        ]);
    }

    // Transfer lagao: checks -> maal kam -> transfer save (naya ya purana)
    // Yeh function sirf DB::transaction ke andar bulana hai
    private function applyTransfer(array $data, ?StockTransfer $transfer = null): void
    {
        // Pehle product, phir batch lock (hamesha yehi tarteeb)
        $productId = StockIn::whereKey($data['stock_in_id'])->value('product_id');
        $product   = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
        $stockIn   = StockIn::whereKey($data['stock_in_id'])->lockForUpdate()->firstOrFail();

        if ((int) $stockIn->branch_id === (int) $data['to_branch_id']) {
            throw ValidationException::withMessages([
                'to_branch_id' => 'The destination branch must be different from the stock-in branch.',
            ]);
        }

        if ($data['quantity'] > $stockIn->remaining_quantity || $data['quantity'] > $product->stock) {
            $available = min($stockIn->remaining_quantity, $product->stock);
            throw ValidationException::withMessages([
                'quantity' => "Stock is not available in this branch in the requested quantity. Available: {$available}.",
            ]);
        }

        $stockIn->decrement('remaining_quantity', $data['quantity']);
        $product->decrement('stock', $data['quantity']);

        // Naya transfer ho to reference aur status bhi set hoga
        $transfer ??= new StockTransfer([
            'reference' => 'TRF-' . Str::upper(Str::random(6)),
            'status'    => 'In Transit',
        ]);

        $transfer->fill([
            'product_id'     => $stockIn->product_id,
            'stock_in_id'    => $stockIn->id,
            'quantity'       => $data['quantity'],
            'from_branch_id' => $stockIn->branch_id,
            'to_branch_id'   => $data['to_branch_id'],
        ])->save();
    }

    // Transfer ka maal wapis batch aur product mein daalo
    private function restoreTransferInventory(StockTransfer $transfer): void
    {
        $product = Product::whereKey($transfer->product_id)->lockForUpdate()->firstOrFail();

        if ($transfer->stock_in_id) {
            StockIn::whereKey($transfer->stock_in_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->increment('remaining_quantity', $transfer->quantity);
        }

        $product->increment('stock', $transfer->quantity);
    }

    // Ek transfer delete: lock -> maal wapis -> delete (ek transaction mein)
    private function removeTransfer(int $id): void
    {
        DB::transaction(function () use ($id) {
            $transfer = StockTransfer::whereKey($id)->lockForUpdate()->first();

            if ($transfer) {
                $this->restoreTransferInventory($transfer);
                $transfer->delete();
            }
        });
    }
}