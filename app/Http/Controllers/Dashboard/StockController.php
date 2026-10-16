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
use Illuminate\Pagination\LengthAwarePaginator;
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
        $details = collect($stockIn->variation_details ?? [])
            ->map(fn ($v) => trim(($v['name'] ?? '') . ': ' . ($v['value'] ?? ''), ': '))
            ->filter()
            ->implode(', ');

        if ($details !== '') {
            return $details;
        }

        $product = $stockIn->product;
        $variationIds = $product?->variation_ids ?: ($product?->variation_id ? [$product->variation_id] : []);
        $variationName = $stockIn->variation?->name;
        if (! $variationName && count($variationIds) === 1) {
            $variationName = trim((string) ($product?->variation ?? '')) ?: null;
        }
        $variationValues = collect($product?->variation_types ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();
        if ($variationValues->isEmpty() && filled($product?->variation_type)) {
            $variationValues = collect([trim((string) $product->variation_type)]);
        }

        $matchesProductKey = count($variationIds) === 1
            && (! $stockIn->variation_id || (int) $variationIds[0] === (int) $stockIn->variation_id);
        if ($variationName && $matchesProductKey && $variationValues->isNotEmpty()) {
            return $variationName . ': ' . $variationValues->implode(', ');
        }

        return $variationName ?: '-';
    }

    private function outOfStockGroupKey(StockIn $stockIn): string
    {
        $details = collect($stockIn->variation_details ?? [])
            ->map(fn ($variation) => [
                trim((string) ($variation['name'] ?? '')),
                trim((string) ($variation['value'] ?? '')),
            ])
            ->filter(fn ($variation) => $variation[0] !== '' || $variation[1] !== '')
            ->sortBy(fn ($variation) => mb_strtolower($variation[0]) . "\0" . mb_strtolower($variation[1]))
            ->values()
            ->all();

        if ($details === [] && $stockIn->variation_id === null) {
            $details = [$stockIn->variation?->name ?? ''];
        }

        return serialize([(int) $stockIn->product_id, $stockIn->variation_id, $details]);
    }

    private function stockInGroupKey(StockIn $stockIn): string
    {
        $details = collect($stockIn->variation_details ?? [])
            ->map(fn ($variation) => [
                trim((string) ($variation['name'] ?? '')),
                trim((string) ($variation['value'] ?? '')),
            ])
            ->filter(fn ($variation) => $variation[0] !== '' || $variation[1] !== '')
            ->sortBy(fn ($variation) => mb_strtolower($variation[0]) . "\0" . mb_strtolower($variation[1]))
            ->values()
            ->all();

        if ($details === [] && $stockIn->variation_id === null) {
            $details = [$stockIn->variation?->name ?? ''];
        }

        return serialize([
            $this->transferProductCode($stockIn->product),
            $stockIn->variation_id,
            $details,
            (int) $stockIn->branch_id,
        ]);
    }

    /* =====================================================
     |  STOCK IN
     ===================================================== */

    // Woh batches jin mein maal bacha hai
    public function in(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $branchId = $user->active_branch_id;
        $applyBranchFilter = function ($query) use ($isAdmin, $branchId) {
            if ($isAdmin) {
                return $query;
            }

            return $branchId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('branch_id', $branchId);
        };

        $query = StockIn::with(['product.category', 'purchase.supplier', 'branch', 'variation'])
            ->where('remaining_quantity', '>', 0);
        $applyBranchFilter($query);

        $groups = $this->searchByProduct($query, $request)
            ->latest('updated_at')
            ->get()
            ->groupBy(fn (StockIn $stockIn) => $this->stockInGroupKey($stockIn))
            ->map(function ($records) {
                $latest = $records->first();
                $quantity = $records->sum(fn (StockIn $stockIn) => (int) $stockIn->quantity);
                $costTotal = $records->sum(
                    fn (StockIn $stockIn) => (float) $stockIn->cost_price * (int) $stockIn->quantity
                );

                return [
                    'id'                 => $latest->id,
                    'stock_in_id'        => $latest->id,
                    'product_id'         => $latest->product_id,
                    'product'            => $latest->product->name,
                    'variation'          => $this->variationLabel($latest),
                    'variation_id'       => $latest->variation_id,
                    'currency'           => $latest->product->currency ?: 'PKR',
                    'branch_id'          => $latest->branch_id,
                    'branch'             => $latest->branch->name,
                    'purchase_id'        => $latest->purchase_id,
                    'purchase'           => $latest->purchase?->purchase_no ?: 'Manual',
                    'batch_no'           => $latest->batch_no,
                    'cost_price'         => $quantity > 0 ? $costTotal / $quantity : 0,
                    'quantity'           => $quantity,
                    'remaining_quantity' => $records->sum(
                        fn (StockIn $stockIn) => (int) $stockIn->remaining_quantity
                    ),
                    'created_at'         => $latest->created_at->format('Y-m-d H:i:s'),
                    'updated_at'         => $latest->updated_at->format('Y-m-d H:i:s'),
                    'sort_date'          => $latest->updated_at,
                ];
            })
            ->sortByDesc('sort_date')
            ->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $stockIns = new LengthAwarePaginator(
            $groups->forPage($currentPage, $perPage)->values(),
            $groups->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );
        $rows = $stockIns->getCollection()
            ->map(fn (array $row) => collect($row)->except('sort_date')->all())
            ->all();

        return $this->page('Stock-In', 'stock-in', $stockIns, $rows, [
            'total_units' => $applyBranchFilter(StockIn::where('remaining_quantity', '>', 0))
                ->sum('remaining_quantity'),
            'this_month'  => $applyBranchFilter(StockIn::where('remaining_quantity', '>', 0))
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
        $matchingBatches = StockIn::with('variation')
            ->where('product_id', $stockIn->product_id)
            ->where('branch_id', $stockIn->branch_id)
            ->get()
            ->filter(fn (StockIn $batch) => $this->outOfStockGroupKey($batch) === $this->outOfStockGroupKey($stockIn));

        $stockSummary = [
            'quantity' => $matchingBatches->sum(fn (StockIn $batch) => (int) $batch->quantity),
            'remaining_quantity' => $matchingBatches->sum(
                fn (StockIn $batch) => (int) $batch->remaining_quantity
            ),
        ];
        $incomingTransfer = StockTransfer::with('fromBranch')
            ->where('destination_stock_in_id', $stockIn->id)
            ->latest()
            ->first();

        $barcodeGenerator = new BarcodeGeneratorHTML();
        $barcode = $stockIn->product?->code
            ? $barcodeGenerator->getBarcode($stockIn->product->code, $barcodeGenerator::TYPE_CODE_128)
            : null;

        return view('stock.details', [
            'purchaseItem'   => $stockIn,
            'variationLabel' => $this->variationLabel($stockIn),
            'barcode'        => $barcode,
            'incomingTransfer' => $incomingTransfer,
            'stockSummary'   => $stockSummary,
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
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $branchId = $user->active_branch_id;
        $query = SoldItem::with(['product', 'sale', 'stockIn.branch']);

        if (! $isAdmin) {
            if ($branchId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('stockIn', fn ($stockIn) => $stockIn->where('branch_id', $branchId));
            }
        }

        $soldItems = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $soldItems->getCollection()->map(function (SoldItem $i) use ($isAdmin) {
            $row = [
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
            ];

            if ($isAdmin) {
                $row['branch'] = $i->stockIn?->branch?->name ?? '-';
            }

            return $row;
        })->all();

        return $this->page('Sold Items', 'sold-items', $soldItems, $rows, [
            'show_branch' => $isAdmin,
        ]);
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
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $branchId = $user->active_branch_id;
        $query = StockIn::with(['product', 'purchase', 'branch']);

        if (! $isAdmin) {
            if ($branchId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('branch_id', $branchId);
            }
        }

        $candidateProductIds = $this->searchByProduct(
            (clone $query)->where('remaining_quantity', '<=', 0),
            $request
        )
            ->distinct()
            ->pluck('product_id');

        $outOfStockRecords = (clone $query)
            ->whereIn('product_id', $candidateProductIds)
            ->latest()
            ->get()
            ->groupBy(fn (StockIn $stockIn) => $this->stockInGroupKey($stockIn))
            ->filter(fn ($records) => $records->sum('remaining_quantity') <= 0)
            ->flatten(1)
            ->values();

        $groups = $outOfStockRecords
            ->groupBy(fn (StockIn $stockIn) => $this->outOfStockGroupKey($stockIn))
            ->map(function ($records) {
                $latest = $records->first();

                return [
                    'date'        => $latest->purchase?->purchase_date?->format('d M Y')
                        ?: $latest->created_at->format('d M Y'),
                    'reference'   => $records->count() > 1
                        ? $records->count() . ' batches'
                        : $latest->batch_no,
                    'product'     => $latest->product->name,
                    'branches'    => $records->pluck('branch.name')->filter()->unique()->implode(', ') ?: '-',
                    'quantity'    => $records->sum(fn (StockIn $stockIn) => (int) $stockIn->remaining_quantity),
                    'currency'    => $latest->product->currency ?: 'PKR',
                    'stock_in_id' => $latest->id,
                    'product_id'  => $latest->product_id,
                    'sort_date'   => $latest->created_at,
                ];
            })
            ->sortByDesc('sort_date')
            ->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $stockIns = new LengthAwarePaginator(
            $groups->forPage($currentPage, $perPage)->values(),
            $groups->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );
        $rows = $stockIns->getCollection()
            ->map(fn (array $row) => collect($row)->except('sort_date')->all())
            ->all();

        return $this->page('Out of Stock', 'out-of-stock', $stockIns, $rows);
    }

    public function destroyOutOfStock(StockIn $stockIn)
    {
        $back = redirect()->route('stock.out-of-stock');

        if ($stockIn->remaining_quantity > 0) {
            return $back->with('error', 'Only out-of-stock products can be deleted from this page.');
        }

        $groupKey = $this->outOfStockGroupKey($stockIn);
        $deleted = 0;
        $skipped = 0;

        StockIn::with('variation')
            ->where('product_id', $stockIn->product_id)
            ->where('remaining_quantity', '<=', 0)
            ->get()
            ->filter(fn (StockIn $record) => $this->outOfStockGroupKey($record) === $groupKey)
            ->each(function (StockIn $record) use (&$deleted, &$skipped): void {
                if ($this->deleteStockIn($record)) {
                    $skipped++;
                } else {
                    $deleted++;
                }
            });

        if ($deleted === 0) {
            return $back->with('error', 'No matching out-of-stock batches could be deleted. They may be linked to sales or stock transfers.');
        }

        $message = "{$deleted} matching out-of-stock batches deleted successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} batches linked to sales or stock transfers were kept.";
        }

        return $back->with('success', $message);
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
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $branchId = $user->active_branch_id;
        $applyBranchFilter = function ($query) use ($isAdmin, $branchId) {
            if ($isAdmin) {
                return $query;
            }

            return $branchId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('from_branch_id', $branchId);
        };

        $query = StockTransfer::with(['product', 'fromBranch', 'toBranch', 'stockIn.variation', 'stockIn.product']);
        $applyBranchFilter($query);

        $transfers = $this->searchByProduct($query, $request)
            ->latest()->paginate(10)->withQueryString();

        $rows = $transfers->getCollection()->map(fn (StockTransfer $t) => [
            'id'             => $t->id,
            'date'           => $t->created_at->format('d M Y'),
            'reference'      => $t->reference,
            'product'        => $t->product->name,
            'variation'      => $this->variationLabel($t->stockIn),
            'quantity'       => $t->quantity,
            'from_branch_id' => $t->from_branch_id,
            'to_branch_id'   => $t->to_branch_id,
            'from'           => $t->fromBranch->name,
            'to'             => $t->toBranch->name,
            'status'         => $t->status,
        ])->all();

        return $this->page('Stock-Transfer', 'stock-transfer', $transfers, $rows, [
            'branches'    => Branch::where('status', 'Active')->orderBy('name')->get()->values(),
            'total_units' => $applyBranchFilter(StockTransfer::query())->sum('quantity'),
            'pending'     => $applyBranchFilter(
                StockTransfer::whereIn('status', ['Pending', 'In Transit'])
            )->count(),
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
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $branchId = $user->active_branch_id;
        $stockIns = StockIn::with(['product', 'branch', 'variation'])
            ->where(function ($q) use ($transfer, $branchId, $isAdmin) {
                $q->where('remaining_quantity', '>', 0);
                if (! $isAdmin) {
                    if ($branchId === null) {
                        $q->whereRaw('1 = 0');
                    } else {
                        $q->where('branch_id', $branchId);
                    }
                }
                // edit mein purani batch bhi dikhao chahe uska maal 0 ho
                if ($transfer?->stock_in_id !== null) {
                    $q->orWhere('id', $transfer->stock_in_id);
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

        try {
            DB::transaction(function () use ($data, $transfer) {
                $transfer = StockTransfer::whereKey($transfer->id)->lockForUpdate()->firstOrFail();

                $this->restoreTransferInventory($transfer);
                $this->applyTransfer($data, $transfer);
            });
        } catch (ValidationException $e) {
            return redirect()->route('stock.transfer')
                ->with('error', $e->validator->errors()->first());
        }

        return redirect()->route('stock.transfer')->with('success', 'Stock transfer updated successfully.');
    }

    public function destroyTransfer(StockTransfer $transfer)
    {
        try {
            $this->removeTransfer($transfer->id);
        } catch (ValidationException $e) {
            return redirect()->route('stock.transfer')
                ->with('error', $e->validator->errors()->first());
        }

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

    private function transferProductCode(Product $product): string
    {
        $code = trim((string) $product->code);
        if ($code === '') {
            $code = 'PRODUCT-' . Str::upper(substr(hash('sha256', mb_strtolower(trim($product->name))), 0, 20));
        }

        return preg_replace('/__BRANCH_\d+$/i', '', $code) ?: $code;
    }

    private function transferBranchProductCode(Product $product, int $branchId): string
    {
        $baseCode = $this->transferProductCode($product);
        $suffix = '__BRANCH_' . $branchId;

        if (strlen($baseCode) + strlen($suffix) > 255) {
            $baseCode = substr($baseCode, 0, 180) . '_' . substr(hash('sha256', $baseCode), 0, 20);
        }

        return $baseCode . $suffix;
    }

    private function transferBranchProductSlug(Product $product, int $branchId): string
    {
        $baseSlug = preg_replace('/-branch-\d+$/i', '', (string) $product->slug);

        return substr($baseSlug, 0, 220) . '-branch-' . $branchId;
    }

    private function assertTransferSourceBranch(int $sourceBranchId): void
    {
        $user = auth()->user();
        if (! $user->isAdmin() && (int) $user->active_branch_id !== $sourceBranchId) {
            throw ValidationException::withMessages([
                'stock_in_id' => 'You can only transfer stock from your active branch.',
            ]);
        }
    }

    // Transfer lagao: checks -> maal kam -> transfer save (naya ya purana)
    // Yeh function sirf DB::transaction ke andar bulana hai
    private function applyTransfer(array $data, ?StockTransfer $transfer = null): void
    {
        // Lock the branch product before its source stock-in batch.
        $productId = StockIn::whereKey($data['stock_in_id'])->value('product_id');
        $product   = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
        $stockIn   = StockIn::whereKey($data['stock_in_id'])->lockForUpdate()->firstOrFail();
        $sourceBranchId = (int) $stockIn->branch_id;

        $this->assertTransferSourceBranch($sourceBranchId);

        if ($product->branch_id !== null && (int) $product->branch_id !== $sourceBranchId) {
            throw ValidationException::withMessages([
                'stock_in_id' => 'The selected stock-in batch does not belong to its product branch.',
            ]);
        }

        if ($sourceBranchId === (int) $data['to_branch_id']) {
            throw ValidationException::withMessages([
                'to_branch_id' => 'The destination branch must be different from the stock-in branch.',
            ]);
        }

        $quantity = (int) $data['quantity'];
        if ($quantity <= 0 || $quantity > (int) $stockIn->remaining_quantity || $quantity > (int) $product->stock) {
            $available = min((int) $stockIn->remaining_quantity, (int) $product->stock);
            throw ValidationException::withMessages([
                'quantity' => "Stock is not available in this branch in the requested quantity. Available: {$available}.",
            ]);
        }

        $baseCode = $this->transferProductCode($product);
        $destinationCode = $this->transferBranchProductCode($product, (int) $data['to_branch_id']);
        $destinationProduct = Product::where('branch_id', $data['to_branch_id'])
            ->whereIn('code', [$baseCode, $destinationCode])
            ->lockForUpdate()
            ->first();

        if (! $destinationProduct && preg_match('/__BRANCH_\d+$/i', (string) $product->code)) {
            $destinationProduct = Product::where('branch_id', $data['to_branch_id'])
                ->where('name', $product->name)
                ->where('category_id', $product->category_id)
                ->lockForUpdate()
                ->first();
        }

        if (! $destinationProduct) {
            $destinationProduct = $product->replicate();
            $destinationProduct->branch_id = $data['to_branch_id'];
            $destinationProduct->stock = 0;
            $destinationProduct->code = $destinationCode;
            $destinationProduct->slug = $this->transferBranchProductSlug($product, (int) $data['to_branch_id']);
            $destinationProduct->save();
        }

        $transfer = $transfer ?: new StockTransfer();
        $transfer->fill([
            'reference' => $transfer->reference ?: 'TRF-' . Str::upper(Str::random(6)),
            'product_id' => $product->id,
            'stock_in_id' => $stockIn->id,
            'destination_stock_in_id' => null,
            'quantity' => $quantity,
            'from_branch_id' => $sourceBranchId,
            'to_branch_id' => $data['to_branch_id'],
            'status' => 'Completed',
        ]);
        $transfer->save();

        $stockIn->decrement('remaining_quantity', $quantity);
        $product->decrement('stock', $quantity);
        $destinationProduct->increment('stock', $quantity);

        $destinationStockIn = StockIn::create([
            'purchase_id'        => $stockIn->purchase_id,
            'product_id'         => $destinationProduct->id,
            'variation_id'       => $stockIn->variation_id,
            'variation_details'  => $stockIn->variation_details,
            'branch_id'          => $data['to_branch_id'],
            'batch_no'           => $transfer->reference,
            'quantity'           => $quantity,
            'remaining_quantity' => $quantity,
            'cost_price'         => $stockIn->cost_price,
        ]);

        $transfer->destination_stock_in_id = $destinationStockIn->id;
        $transfer->save();

        DB::table('stock_transfer_allocations')->insert([
            'stock_transfer_id' => $transfer->id,
            'source_stock_in_id' => $stockIn->id,
            'destination_stock_in_id' => $destinationStockIn->id,
            'quantity' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Transfer ka maal wapis batch aur product mein daalo
    private function restoreTransferInventory(StockTransfer $transfer): void
    {
        $sourceProduct = Product::whereKey($transfer->product_id)->lockForUpdate()->firstOrFail();
        $sourceStockIn = $transfer->stock_in_id
            ? StockIn::whereKey($transfer->stock_in_id)->first()
            : null;
        $this->assertTransferSourceBranch($sourceStockIn
            ? (int) $sourceStockIn->branch_id
            : (int) $transfer->from_branch_id);

        $allocations = DB::table('stock_transfer_allocations')
            ->where('stock_transfer_id', $transfer->id)
            ->lockForUpdate()
            ->get();

        if ($allocations->isNotEmpty()) {
            foreach ($allocations as $allocation) {
                StockIn::whereKey($allocation->source_stock_in_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('remaining_quantity', $allocation->quantity);
            }
        } elseif ($transfer->stock_in_id) {
            StockIn::whereKey($transfer->stock_in_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->increment('remaining_quantity', $transfer->quantity);
        }

        $destinationStockIn = $transfer->destinationStockIn()
            ->lockForUpdate()
            ->first();

        if ($destinationStockIn) {
            if ((int) $destinationStockIn->remaining_quantity < (int) $transfer->quantity) {
                throw ValidationException::withMessages([
                    'transfer' => 'This transfer cannot be changed or deleted because some transferred stock has already been used.',
                ]);
            }

            $transfer->destination_stock_in_id = null;
            $transfer->save();
            $destinationStockIn->decrement('quantity', $transfer->quantity);
            $destinationStockIn->decrement('remaining_quantity', $transfer->quantity);

            $destinationProduct = Product::whereKey($destinationStockIn->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($destinationProduct->id !== $sourceProduct->id) {
                if ((int) $destinationProduct->stock < (int) $transfer->quantity) {
                    throw ValidationException::withMessages([
                        'transfer' => 'This transfer cannot be changed or deleted because the destination product stock has already been used.',
                    ]);
                }
                $destinationProduct->decrement('stock', $transfer->quantity);
                $sourceProduct->increment('stock', $transfer->quantity);
            }
        } elseif ($transfer->status !== 'Completed') {
            // Older pending transfers did not create a destination product or batch.
            $sourceProduct->increment('stock', $transfer->quantity);
        }

        DB::table('stock_transfer_allocations')
            ->where('stock_transfer_id', $transfer->id)
            ->delete();

        $transfer->destination_stock_in_id = null;
        $transfer->quantity = 0;
        $transfer->save();
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