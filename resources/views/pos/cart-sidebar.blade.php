<!-- Detailed POS cart table and payment summary. -->
@php
    $cartSubtotal = (float) $productItem->sum(function ($item) {
        return (float) ($item->options->original_price ?? $item->price) * $item->qty;
    });
    $cartTax = (float) $productItem->sum(function ($item) {
        return (float) ($item->options->tax ?? 0) * $item->qty;
    });
    $cartDiscount = (float) $productItem->sum(function ($item) {
        return (float) ($item->options->discount ?? 0) * $item->qty;
    });
    $cartGrandTotal = max(0, round($cartSubtotal - $cartDiscount + $cartTax, 2));

    // Summary accumulators — must be initialized before the foreach loop
    $summaryPrice     = 0;
    $summaryNetAmount = 0;
    $summaryTaxAmount = 0;
    $summaryDiscount  = 0;
@endphp
<div class="cart-items-wrapper position-relative" style="height: 350px; overflow-y: auto; overflow-x: hidden;">
    @if ($productItem->count() > 0)
        <div class="table-responsive pos-cart-table-wrap">
            <table class="table table-sm mb-0 pos-cart-table rounded-0 mt-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price (PKR)</th>
                        <th>Quantity</th>
                        <th>Gross Amount (PKR)</th>
                        <th>Tax Rate(%)</th>
                        <th>Tax Amount</th>
                        <th>Amount Incl. Tax</th>
                        <th>Discount (PKR)</th>
                        <th>Net Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productItem as $item)
                        @php
                            $options = $item->options;
                            $discount = (float) ($options->discount ?? 0);
                            $originalPrice = (float) ($options->original_price ?? $item->price);
                            $stock = $options->stock ?? null;
                            $currency = $options->currency ?? 'PKR';
                            $image = $options->image ?? asset('assets/images/product/default.webp');
                        @endphp
                        @php
                            $gross     = $originalPrice * $item->qty;
                            $taxRate   = (float) ($options->tax_rate ?? 10);
                            $taxAmount = $gross * $taxRate / 100;
                            $inclTax   = $gross + $taxAmount;
                            $netAmount = max(0, $inclTax - $discount);
                            $summaryPrice     += $originalPrice;
                            $summaryNetAmount += $netAmount;
                            $summaryTaxAmount += $taxAmount;
                            $summaryDiscount  += $discount;
                        @endphp
                        <tr data-row-id="{{ $item->rowId }}" data-item-id="{{ $item->id }}"
                            data-unit-price="{{ $originalPrice }}" data-currency="{{ $currency }}">
                            <td>
                                <img class="pos-cart-product-image" src="{{ $image }}" alt="{{ $item->name }}">
                            </td>
                            <td class="pos-cart-product-name">
                                <div>{{ $item->name }}</div>
                            </td>
                            <td>
                                <div>{{ number_format($originalPrice, 2) }}</div>
                            </td>
                            <td>
                                <div class="pos-quantity-control">
                                    <input type="number" class="form-control form-control-sm pos-quantity-input"
                                        style="width: 50px;"
                                        value="{{ $item->qty }}" min="0" @if ($stock !== null) max="{{ $stock }}" @endif step="1" aria-label="Quantity"
                                        data-saved="{{ $item->qty }}">
                                </div>
                            </td>
                            <td><span class="pos-gross-amount">{{ number_format($gross, 2) }}</span></td>
                            <td>
                                <input type="number" class="form-control form-control-sm pos-tax-rate-input"
                                    style="width: 65px;" value="{{ number_format($taxRate, 2) }}"
                                    min="0" max="100" step="0.01">
                            </td>
                            <td><span class="pos-tax-amount">{{ number_format($taxAmount, 2) }}</span></td>
                            <td><span class="pos-incl-tax-amount">{{ number_format($inclTax, 2) }}</span></td>
                            <td>
                                <input type="number" class="form-control form-control-sm pos-discount-input"
                                    style="width: 80px;" value="{{ number_format($discount, 2) }}"
                                    min="0" step="0.01" max="{{ $inclTax }}">
                            </td>
                            <td><span class="pos-net-amount">{{ $currency }} {{ number_format($netAmount, 2) }}</span></td>
                            <td>
                                <button type="button" class="btn btn-link text-danger p-0" title="Remove product"
                                    data-action="remove">
                                    <x-heroicon-o-trash class="w-5 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
            <div class="bg-light rounded-circle p-3 mb-3">
                <x-heroicon-o-shopping-bag class="w-8 h-8 text-secondary" />
            </div>
            <p class="mb-0 font-weight-medium">Add at least one product</p>
            <small>Select a product from the right panel</small>
        </div>
    @endif
</div>

<div class="pos-cart-summary p-3 bg-white border-top">
    @php($cartCurrency = $productItem->first()?->options?->currency ?? 'PKR')
    <div class="row">
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Item</label>
            <input class="form-control form-control-sm pos-summary-items" value="{{ Cart::count() }}" readonly>
        </div>
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Price</label>
            <input class="form-control form-control-sm"
                value="{{ $cartCurrency }} {{ number_format($cartSubtotal, 2) }}" readonly>
        </div>
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Tax</label>
            <input class="form-control form-control-sm"
                value="{{ $cartCurrency }} {{ number_format($cartTax, 2) }}" readonly>
        </div>
        <div class="col-md-3 col-6 form-group">
            <label>Discount</label>
            <input class="form-control form-control-sm"
                value="{{ $cartCurrency }} {{ number_format($cartDiscount, 2) }}"
                readonly>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
        <span class="font-weight-bold">Grand Total</span>
        <span class="font-weight-bold text-primary" id="cart-total">{{ $cartCurrency }}
            {{ number_format($cartGrandTotal, 2) }}</span>
    </div>
</div>

<div class="p-3 bg-white border-top">
    @if (Cart::count() > 0)
        <div class="row">
            <div class="col-6 pr-1">
                <label class="small font-weight-bold text-muted mb-1">Payment Method</label>
                <select class="form-control form-control-sm" id="payment_type">
                    <option value="Cash" selected>Cash</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>
            <div class="col-6 pl-1">
                <label class="small font-weight-bold text-muted mb-1 ">Amount Paid</label>
                <input type="number" class="form-control form-control-sm" id="pay_amount" placeholder="0"
                    oninput="calculateChange()" min="0">
            </div>
        </div>
        {{-- <div class="d-flex justify-content-between align-items-center my-3 px-2 py-2 bg-light rounded">
            <span class="small font-weight-bold text-muted">Change</span>
            <span class="font-weight-bold text-success" id="change_amount">{{ $cartCurrency }} 0.00</span>
        </div> --}}
        <button type="button"
            class="btn btn-primary btn-lg btn-block rounded-pill shadow-lg d-flex align-items-center justify-content-center  my-3 px-2 py-2"
            onclick="validateAndShowModal()">
            <span class="mr-2">Make Payment</span>
            <x-heroicon-o-arrow-right class="w-5 h-5" />
        </button>
    @else
        <button type="button" class="btn btn-light btn-lg btn-block rounded-pill text-muted" disabled>Start
            Sale</button>
    @endif
</div>