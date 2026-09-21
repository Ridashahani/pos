<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Requests\Sale\StoreSaleRequest;

class SaleController extends Controller
{
    /**
     * Display a listing of pending sales.
     */
    public function pendingSales()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $sales = QueryBuilder::for(Sale::class)
            ->where('sale_status', 'pending')
            ->allowedSorts([
                'sale_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'sales.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('sales.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('sales.pending-sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Display a listing of complete sales.
     */
    public function completeSales()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $sales = QueryBuilder::for(Sale::class)
            ->where('sale_status', 'complete')
            ->allowedSorts([
                'sale_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'sales.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('sales.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('sales.complete-sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Store a newly created sale in storage.
     */
    public function storeSale(StoreSaleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $invoice_no = IdGenerator::generate([
                'table' => 'sales',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-'
            ]);

            $total = (float) Cart::total(null, null, '');
            $pay_amount = $request->pay_amount;
            $due_amount = $total - $pay_amount;

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'invoice_no' => $invoice_no,
                'sale_date' => Carbon::now(),
                'sale_status' => 'pending',
                'total_products' => Cart::count(),
                'sub_total' => (float) Cart::subtotal(null, null, ''),
                'vat' => (float) Cart::tax(null, null, ''),
                'total' => $total,
                'payment_type' => $request->payment_type,
                'pay_amount' => $pay_amount,
                'due_amount' => $due_amount,
            ]);

            // Create Sale Details
            $contents = Cart::content();
            foreach ($contents as $content) {
                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'product_id' => $content->id,
                    'quantity' => $content->qty,
                    'unit_price' => $content->price,
                    'currency' => $content->options->currency ?? 'PKR',
                    'discount' => (float) ($content->options->discount ?? 0) * $content->qty,
                    'total' => $content->total,
                ]);
            }

            // Clear Cart
            Cart::destroy();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale created successfully!',
                    'invoice_url' => route('sale.invoiceDownload', $sale->id),
                    'cart_html' => view('pos.cart-sidebar', ['productItem' => Cart::content()])->render(),
                    'cart_count' => Cart::count(),
                ]);
            }

            return Redirect::route('sale.invoiceDownload', $sale->id)->with('success', 'Sale has been created!');
        });
    }

    /**
     * Display the specified sale resource.
     */
    public function saleDetails(int $sale_id)
    {
        $sale = Sale::with('customer')->findOrFail($sale_id);
        $saleDetails = SaleDetails::with('product')
                        ->where('sale_id', $sale_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('sales.details-sale', [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
           
        ]);
    }

    /**
     * Update the specified sale resource status in storage.
     */
    public function updateStatus(Request $request)
    {
        $sale_id = $request->id ?? $request->sale_id;

        DB::transaction(function () use ($sale_id) {
            $sale = Sale::whereKey($sale_id)->lockForUpdate()->firstOrFail();

            if ($sale->sale_status !== 'complete') {
                $products = SaleDetails::where('sale_id', $sale_id)->get();

                foreach ($products as $detail) {
                    $product = Product::whereKey($detail->product_id)->lockForUpdate()->firstOrFail();

                    if ($product->stock < $detail->quantity) {
                        throw ValidationException::withMessages([
                            'stock' => "Insufficient stock for {$product->name}.",
                        ]);
                    }

                    $product->decrement('stock', $detail->quantity);
                }

                $sale->update(['sale_status' => 'complete']);
            }
        });

        return Redirect::route('sale.pendingSales')->with('success', 'Sale has been completed!');
    }

    public function invoiceDownload(int $sale_id)
    {
        $sale = Sale::with('customer')->findOrFail($sale_id);
        $saleDetails = SaleDetails::with('product')
            ->where('sale_id', $sale_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('pos.print-invoice', [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
           
        ]);
    }

    public function printReceipt(int $sale_id)
    {
        $sale = Sale::with('customer')->findOrFail($sale_id);
        $saleDetails = SaleDetails::with('product')
                        ->where('sale_id', $sale_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('pos.print-receipt', [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
        ]);
    }

    public function pendingDue()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $sales = QueryBuilder::for(Sale::class)
            ->where('due_amount', '>', 0)
            ->allowedSorts([
                'sale_date',
                'due_amount',
                'pay_amount',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'sales.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('sales.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('sales.pending-due', [
            'sales' => $sales,
        ]);
    }

    public function saleDueAjax(int $id)
    {
        $sale = Sale::findOrFail($id);

        return response()->json($sale);
    }

    public function updateDue(Request $request)
    {
        $request->validate([
            'due_amount' => 'required|numeric',
        ]);

        $sale_id = $request->sale_id;
        if (!$sale_id) {
            abort(400, 'Sale ID is required.');
        }

        $sale = Sale::findOrFail($sale_id);
        $mainPay = $sale->pay_amount;
        $mainDue = $sale->due_amount;

        $paid_due = $mainDue - $request->due_amount;
        $paid_pay = $mainPay + $request->due_amount;

        $sale->update([
            'due_amount' => $paid_due,
            'pay_amount' => $paid_pay,
        ]);

        return Redirect::route('sale.pendingDue')->with('success', 'Due Amount Updated Successfully!');
    }

   

  
}
