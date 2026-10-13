<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    private const PRODUCTS_PER_PAGE = 30;

    /**
     * Cart lines in the order they were added.
     * (Gloudemans re-adds a row at the end whenever its options change,
     * so we sort by our own stable "added_at" option.)
     */
    private function cartContent()
    {
        return Cart::content()->sortBy(fn($item) => $item->options->added_at ?? 0);
    }

    /**
     * Full cart response (re-renders the cart partial).
     * Only used when the table structure changes: add / remove / missing row.
     */
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
     * Lightweight response for qty / tax / discount edits.
     * The browser already shows the right numbers (it calculates locally), so all it
     * needs back is the row's new id: no view rendering, tiny payload.
     */
    private function lineJson(CartItem $item)
    {
        return response()->json([
            'success'    => true,
            'row_id'     => $item->rowId,
            'cart_count' => Cart::count(),
        ]);
    }

    /**
     * Single place that recalculates a cart line, so qty / tax / discount
     * can never disagree with each other.
     */
    private function applyLine(string $rowId, int $qty, array $options): CartItem
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

        return Cart::update($rowId, [
            'qty'     => $qty,
            'price'   => $net / ($qty * (1 + $taxRate / 100)),
            'options' => $options,
        ]);
    }

    /**
     * Display the POS interface.
     *
     * Products are NOT loaded here any more: the dropdown fetches them on demand
     * from searchProducts(), so the page stays light however big the catalogue is.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->roles()->whereRaw('LOWER(name) = ?', ['admin'])->exists();
        $branches = $isAdmin
            ? Branch::where('status', 'Active')->orderBy('name')->get()
            : $user->branches()->where('branches.status', 'Active')->orderBy('branches.name')->get();

        return view('pos.index', [
            'branches' => $branches,
            'selectedBranchId' => old('branch_id', $branches->count() === 1 ? $branches->first()->id : null),
            'productItem' => $this->cartContent(),
        ]);
    }

    /**
     * Product search for the Select2 dropdown (AJAX, paginated, 30 per page).
     * Same sellable rules as before: in stock and not expired.
     * Exact code matches come first, then names starting with the term, then the rest.
     */
    public function searchProducts(Request $request)
    {
        $data = $request->validate([
            'term' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $term = trim($data['term'] ?? '');

        $query = Product::query()
            ->without('category')
            ->where('stock', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expire_date')
                    ->orWhere('expire_date', '>=', Carbon::today());
            });

        if ($term !== '') {
            $escaped = addcslashes($term, '\\%_');

            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('code', 'like', "%{$escaped}%");
            })
                ->orderByRaw('(code = ?) desc', [$term])
                ->orderByRaw('(name like ?) desc', [$escaped . '%']);
        }

        // simplePaginate = one query (LIMIT n+1), no COUNT(*).
        $products = $query
            ->orderBy('name')
            ->simplePaginate(self::PRODUCTS_PER_PAGE, ['id', 'name', 'code', 'selling_price', 'stock', 'image']);

        return response()->json([
            'results' => $products->getCollection()->map(fn($p) => [
                'id'    => $p->id,
                'text'  => $p->name . ($p->code ? " ({$p->code})" : ''),
                'name'  => $p->name,
                'code'  => $p->code,
                'price' => $p->selling_price,
                'stock' => $p->stock,
                'image' => Product::imageUrl($p->image),
            ])->values(),
            'pagination' => ['more' => $products->hasMorePages()],
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
            // Only the id is trusted from the browser. Name, price, code and image
            // always come from the database (a cashier can't tamper with the price).
            $data = $request->validate([
                'id' => 'required|integer',
            ]);

            $product = Product::findOrFail($data['id']);
            $stock   = max(0, (int) $product->stock);

            // Adding the same product again increments the existing line.
            $existing = Cart::search(
                fn($cartItem) => $cartItem->id == $product->id && empty($cartItem->options->manual)
            )->first();

            if ($existing) {
                $options = $existing->options->toArray();
                $options['stock'] = $stock;
                $this->applyLine($existing->rowId, min($existing->qty + 1, max(1, $stock)), $options);
            } else {
                $taxRate = (float) $product->gst_tax;
                $price   = (float) $product->selling_price;

                $item = Cart::add([
                    'id'    => $product->id,
                    'name'  => $product->name,
                    'qty'   => 1,
                    'price' => $price,
                    'options' => [
                        'size'           => 'large',
                        'code'           => $product->code ?: 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
                        'image'          => $product->image,
                        'tax'            => 0,
                        'tax_rate'       => $taxRate,
                        'discount'       => 0,
                        'original_price' => $price,
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

        // Line removed (qty 0 or no stock left): the table changes, so send the full cart.
        if ($qty === 0) {
            Cart::remove($rowId);

            return $request->wantsJson()
                ? $this->cartJson('Cart has been updated!')
                : Redirect::back()->with('success', 'Cart has been updated!');
        }

        $line = $this->applyLine($rowId, $qty, $options);

        return $request->wantsJson()
            ? $this->lineJson($line)
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

        return $this->lineJson(
            $this->applyLine($rowId, max(1, (int) $item->qty), $options)
        );
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

        return $this->lineJson(
            $this->applyLine($rowId, max(1, (int) $item->qty), $options)
        );
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