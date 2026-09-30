<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Spatie\QueryBuilder\QueryBuilder;

class PosController extends Controller
{
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
            'productItem' => Cart::content(),
            'products' => QueryBuilder::for(Product::class)
                ->whereHas('stockIns', fn ($query) => $query->where('remaining_quantity', '>', 0))
                ->where('expire_date', '>', $todayDate)
                ->allowedSorts(['name', 'selling_price'])
                ->allowedFilters(['name', 'category_id'])
                ->filter(request(['search', 'category_id']))
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    /**
     * Add item to the cart.
     */
    public function addCart(Request $request)
    {

        if($request->boolean('is_manual')){
        $validatedData = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'tax'=> 'nullable|numeric|min:0',
            'discount'=> 'nullable|numeric|min:0'
        ]);

        $finalPrice = $validatedData['price']+($validatedData['tax']?? 0)-($validatedData['discount']?? 0);

        Cart::add([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'qty' => 1,
            'price' => max($finalPrice. 0),
                'options' => [
                    'size' => 'large',
                    'manual' => true,
                    'code' => 'MANUAL',
                    'image' => null,
                        'original_price' => $validatedData['price'],
                    'tax' => $validatedData['tax'] ?? 0,
                    'discount' => $validatedData['discount'] ?? 0,
                ]
        ]);
        }else{
        $validatedData = $request->validate([
            'id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);
        $product = Product::findOrFail($validatedData['id']);
        $taxRate = (float) ($product->order_tax ?? Setting::get('gst', 0));

        $cartItem = Cart::add([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'qty' => 1,
            'price' => $validatedData['price'],
            'options' => [
                'size' => 'large',
                'code' => $request->input('code', 'PRD-' . str_pad($validatedData['id'], 6, '0', STR_PAD_LEFT)),
                'image' => $request->input('image'),
                'tax' => $request->input('tax', 0),
                'tax_rate' => $taxRate,
                'discount' => $request->input('discount', 0),
                'original_price' => $validatedData['price'],
                'currency' => $product->currency ?: 'PKR',
                'stock' => (int) $product->stock,
            ]
        ]);
        Cart::setTax($cartItem->rowId, $taxRate);
    }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product has been added!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Product has been added!');
    }

    /**
     * Update item quantity in the cart.
     */
    public function updateCart(Request $request, string $rowId)
    {
        $validatedData = $request->validate([
            'qty' => 'required|integer|min:0',
        ]);

        $item = Cart::get($rowId);
        $quantity = $validatedData['qty'];
        $options = $item->options->toArray();

        if (!($item->options->manual ?? false)) {
            $product = Product::findOrFail($item->id);
            $stock = max(0, (int) $product->stock);
            $quantity = min($quantity, $stock);
            $options['stock'] = $stock;
        }

        if ($quantity === 0) {
            Cart::update($rowId, 0);
        } else {
            $originalPrice = (float) ($options['original_price'] ?? $item->price);
            $taxRate = (float) ($options['tax_rate'] ?? 10);
            $gross = $originalPrice * $quantity;
            $taxAmount = $gross * $taxRate / 100;
            $discount = min((float) ($options['discount'] ?? 0), $gross + $taxAmount);
            $options['discount'] = $discount;
            $netAmount = max(0, $gross + $taxAmount - $discount);

            Cart::setTax($rowId, $taxRate);
            Cart::update($rowId, [
                'qty' => $quantity,
                'price' => $netAmount / ($quantity * (1 + $taxRate / 100)),
                'options' => $options,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart has been updated!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Cart has been updated!');
    }

    public function updateDiscount(Request $request, string $rowId)
    {
        $validatedData = $request->validate([
            'discount' => 'required|numeric|min:0',
        ]);
        $item = Cart::get($rowId);

        abort_if(!$item, 404);

        $options = $item->options->toArray();
        $originalPrice = (float) ($options['original_price'] ?? $item->price);
        $quantity = max(1, (int) $item->qty);
        $taxRate = (float) ($options['tax_rate'] ?? 10);
        $gross = $originalPrice * $quantity;
        $taxAmount = $gross * $taxRate / 100;
        $discount = min((float) $validatedData['discount'], $gross + $taxAmount);
        $netAmount = max(0, $gross + $taxAmount - $discount);
        $options['discount'] = $discount;

        Cart::setTax($rowId, $taxRate);
        Cart::update($rowId, [
            'price' => $netAmount / ($quantity * (1 + $taxRate / 100)),
            'options' => $options,
        ]);

        return response()->json([
            'success' => true,
            'cart_html' => view('pos.cart-sidebar', ['productItem' => Cart::content()])->render(),
            'cart_count' => Cart::count(),
        ]);
    }

    public function updateTaxRate(Request $request, string $rowId)
    {
        $validatedData = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $taxRate = (float) $validatedData['tax_rate'];

        Cart::setTax($rowId, $taxRate);
        $item = Cart::get($rowId);
        $options = $item->options->toArray();
        $options['tax_rate'] = $taxRate;

        Cart::update($rowId, ['options' => $options]);

        return response()->json([
            'success' => true,
            'cart_html' => view('pos.cart-sidebar', ['productItem' => Cart::content()])->render(),
            'cart_count' => Cart::count(),
        ]);
    }

    /**
     * Remove item from the cart.
     */
    public function deleteCart(Request $request, string $rowId)
    {
        Cart::remove($rowId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart has been deleted!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Cart has been deleted!');
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
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('phone', 'LIKE', "%{$term}%");
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
