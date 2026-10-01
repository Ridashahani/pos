<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Spatie\QueryBuilder\QueryBuilder;

class PosController extends Controller
{
    /**
     * Cart lines in the order they were added.
     * (Gloudemans re-adds a row at the end whenever its options change,
     * so we sort by our own stable "added_at" option.)
     */
    private function cartContent()
    {
        return Cart::content()->sortBy(fn ($item) => $item->options->added_at ?? 0);
    }

    private function cartJson(?string $message = null, int $status = 200)
    {
        return response()->json([
            'success'    => $status < 400,
            'message'    => $message,
            'cart_html'  => view('pos.cart-sidebar', ['productItem' => $this->cartContent()])->render(),
            'cart_count' => Cart::count(),
        ], $status);
    }

    /**
     * Single place that recalculates a cart line, so qty / tax / discount
     * can never disagree with each other.
     */
    private function applyLine(string $rowId, int $qty, array $options): void
    {
        $original = (float) ($options['original_price'] ?? 0);
        $taxRate  = (float) ($options['tax_rate'] ?? 10);
        $gross    = $original * $qty;
        $tax      = $gross * $taxRate / 100;
        $discount = min(max(0, (float) ($options['discount'] ?? 0)), $gross + $tax);
        $net      = max(0, $gross + $tax - $discount);

        $options['tax_rate'] = $taxRate;
        $options['discount'] = $discount;

        Cart::setTax($rowId, $taxRate);
        Cart::update($rowId, [
            'qty'     => $qty,
            'price'   => $net / ($qty * (1 + $taxRate / 100)),
            'options' => $options,
        ]);
    }

    /**
     * Display the POS interface.
     */
    public function index(Request $request)
    {
        $todayDate = Carbon::today();
        $row = (int) $request->input('row', 10);
        $search = trim((string) $request->input('search', ''));

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $products = QueryBuilder::for(Product::class)
            ->where('products.stock', '>', 0)
            ->where(function ($query) use ($todayDate) {
                $query->whereNull('expire_date')
                    ->orWhereDate('expire_date', '>=', $todayDate);
            })
            ->allowedSorts(['name', 'selling_price'])
            ->allowedFilters(['name', 'category_id'])
            ->filter($request->only(['search', 'category_id']))
            ->orderBy('products.id')
            ->when($search === '', fn ($query) => $query->whereIn('products.id', []))
            ->paginate($row)
            ->appends($request->query());

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('pos.product-grid', compact('products'))->render(),
                'total' => $products->total(),
            ]);
        }

        return view('pos.index', [
            'categories' => Category::orderBy('name')->get(),
            'productItem' => $this->cartContent(),
            'products' => QueryBuilder::for(Product::class)
                ->whereHas('stockIns', fn ($query) => $query->where('remaining_quantity', '>', 0))
                ->where('expire_date', '>', $todayDate)
                ->allowedSorts(['name', 'selling_price'])
                ->allowedFilters(['name', 'category_id'])
                ->filter(request(['search', 'category_id']))
                ->orderBy('products.id')
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    /**
     * Add item to the cart.
     */
    public function addCart(Request $request)
    {
        if ($request->boolean('is_manual')) {
            $data = $request->validate([
                'name'     => 'required|string',
                'price'    => 'required|numeric|min:0',
                'tax'      => 'nullable|numeric|min:0',      // tax AMOUNT (converted to a rate below)
                'discount' => 'nullable|numeric|min:0',
            ]);

            $price = (float) $data['price'];
            $tax   = (float) ($data['tax'] ?? 0);

            $item = Cart::add([
                'id'    => 'manual-' . Str::uuid(),
                'name'  => $data['name'],
                'qty'   => 1,
                'price' => $price,
                'options' => [
                    'size'           => 'large',
                    'manual'         => true,
                    'code'           => 'MANUAL',
                    'image'          => null,
                    'original_price' => $price,
                    'tax_rate'       => $price > 0 ? $tax / $price * 100 : 0,
                    'discount'       => (float) ($data['discount'] ?? 0),
                    'currency'       => 'PKR',
                    'added_at'       => microtime(true),
                ],
            ]);

            $this->applyLine($item->rowId, 1, $item->options->toArray());
        } else {
            $data = $request->validate([
                'id'    => 'required|numeric',
                'name'  => 'required|string',
                'price' => 'required|numeric',
            ]);

            $product = Product::findOrFail($data['id']);
            $stock   = max(0, (int) $product->stock);

            // Adding the same product again increments the existing line.
            $existing = Cart::search(
                fn ($cartItem) => $cartItem->id == $data['id'] && empty($cartItem->options->manual)
            )->first();

            if ($existing) {
                $options = $existing->options->toArray();
                $options['stock'] = $stock;
                $this->applyLine($existing->rowId, min($existing->qty + 1, max(1, $stock)), $options);
            } else {
                $taxRate = (float) ($product->order_tax ?? Setting::get('gst', 0));

                $item = Cart::add([
                    'id'    => $data['id'],
                    'name'  => $data['name'],
                    'qty'   => 1,
                    'price' => $data['price'],
                    'options' => [
                        'size'           => 'large',
                        'code'           => $request->input('code', 'PRD-' . str_pad($data['id'], 6, '0', STR_PAD_LEFT)),
                        'image'          => $request->input('image'),
                        'tax'            => $request->input('tax', 0),
                        'tax_rate'       => $taxRate,
                        'discount'       => $request->input('discount', 0),
                        'original_price' => $data['price'],
                        'currency'       => $product->currency ?: 'PKR',
                        'stock'          => $stock,
                        'added_at'       => microtime(true),
                    ],
                ]);
                Cart::setTax($item->rowId, $taxRate);
            }
        }

        return $request->wantsJson()
            ? $this->cartJson('Product has been added!')
            : Redirect::back()->with('success', 'Product has been added!');
    }

    private function missingCartItemResponse(Request $request)
    {
        if (!$request->wantsJson()) {
            abort(404);
        }

        return $this->cartJson('This cart item is no longer available.', 409);
    }

    /**
     * Update item quantity in the cart.
     */
    public function updateCart(Request $request, string $rowId)
    {
        $data = $request->validate([
            'qty' => 'required|integer|min:0',
        ]);

        if (!Cart::content()->has($rowId)) {
            return $this->missingCartItemResponse($request);
        }

        $item = Cart::get($rowId);
        $qty = (int) $data['qty'];
        $options = $item->options->toArray();

        if (empty($options['manual'])) {
            $stock = max(0, (int) Product::findOrFail($item->id)->stock);
            $qty = min($qty, $stock);
            $options['stock'] = $stock;
        }

        if ($qty === 0) {
            Cart::remove($rowId);
        } else {
            $this->applyLine($rowId, $qty, $options);
        }

        return $request->wantsJson()
            ? $this->cartJson('Cart has been updated!')
            : Redirect::back()->with('success', 'Cart has been updated!');
    }

    public function updateDiscount(Request $request, string $rowId)
    {
        $data = $request->validate([
            'discount' => 'required|numeric|min:0',
        ]);

        if (!Cart::content()->has($rowId)) {
            return $this->missingCartItemResponse($request);
        }

        $item = Cart::get($rowId);
        $options = $item->options->toArray();
        $options['discount'] = (float) $data['discount'];

        $this->applyLine($rowId, max(1, (int) $item->qty), $options);

        return $this->cartJson();
    }

    public function updateTaxRate(Request $request, string $rowId)
    {
        $data = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        if (!Cart::content()->has($rowId)) {
            return $this->missingCartItemResponse($request);
        }

        $item = Cart::get($rowId);
        $options = $item->options->toArray();
        $options['tax_rate'] = (float) $data['tax_rate'];

        $this->applyLine($rowId, max(1, (int) $item->qty), $options);

        return $this->cartJson();
    }

    /**
     * Remove item from the cart.
     */
    public function deleteCart(Request $request, string $rowId)
    {
        if (!Cart::content()->has($rowId)) {
            return $this->missingCartItemResponse($request);
        }

        Cart::remove($rowId);

        return $request->wantsJson()
            ? $this->cartJson('Cart has been deleted!')
            : Redirect::back()->with('success', 'Cart has been deleted!');
    }

    /**
     * Store a newly created Customer (AJAX).
     */
    public function storeCustomer(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'nullable|email|max:50|unique:customers,email',
            'phone' => 'nullable|string|max:15|unique:customers,phone',
            'city' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:100',
        ]);

        $customer = Customer::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully!',
            'customer' => $customer
        ]);
    }

    /**
     * Search Customers for Select2 (AJAX).
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->term;
        $query = Customer::query();

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%");
            });
        }

        $customers = $query->latest()->limit(20)->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . ($customer->phone ?? 'N/A') . ')'
            ];
        });

        return response()->json(['results' => $customers]);
    }
}