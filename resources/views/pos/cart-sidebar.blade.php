<!-- Detailed POS cart table and payment summary. -->
<div class="cart-items-wrapper position-relative" style="height: 350px; overflow-y: auto; overflow-x: hidden;">
    @if ($productItem->count() > 0)
        <div class="table-responsive pos-cart-table-wrap">
            <table class="table table-sm mb-0 pos-cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Price</th>
                        <th>Tax</th>
                        <th>Discount</th>
                        <th>Quantity</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productItem as $item)
                        @php
                            $options = $item->options;
                            $tax = (float) ($options->tax ?? 0);
                            $discount = (float) ($options->discount ?? 0);
                            $originalPrice = (float) ($options->original_price ?? $item->price);
                            $image = $options->image ?? asset('assets/images/product/default.webp');
                        @endphp
                        <tr>
                            <td>
                                <img class="pos-cart-product-image" src="{{ $image }}" alt="{{ $item->name }}">
                            </td>
                            <td class="pos-cart-product-name">{{ $item->name }}</td>
                            <td>{{ $options->code ?? 'PRD-' . str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>PKR {{ number_format($originalPrice, 2) }}</td>
                            <td>{{ number_format($tax, 2) }}</td>
                            <td>
                                <div class="pos-discount-control">
                                    <input type="number" class="form-control form-control-sm pos-discount-input"
                                        value="{{ number_format($discount, 2, '.', '') }}" min="0"
                                        max="{{ $originalPrice + $tax }}" step="0.01" aria-label="Discount">
                                    <button type="button" class="btn btn-primary btn-sm pos-discount-apply"
                                        onclick="applyDiscount('{{ $item->rowId }}', this)">Apply</button>
                                </div>
                            </td>
                            <td>
                                <div class="pos-quantity-control">
                                    <button type="button" class="pos-quantity-button"
                                        onclick="updateCart('{{ $item->rowId }}', {{ max(0, $item->qty - 1) }})">&minus;</button>
                                    <span>{{ $item->qty }}</span>
                                    <button type="button" class="pos-quantity-button pos-quantity-add"
                                        onclick="updateCart('{{ $item->rowId }}', {{ $item->qty + 1 }})">+</button>
                                </div>
                            </td>
                            <td>PKR {{ number_format($item->subtotal, 2) }}</td>
                            <td>
                                <button type="button" class="btn btn-link text-danger p-0" title="Remove product"
                                    onclick="deleteCart('{{ $item->rowId }}')">
                                    <x-heroicon-o-trash class="w-4 h-4" />
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
    <div class="row">
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Item</label>
            <input class="form-control form-control-sm" value="{{ Cart::count() }}" readonly>
        </div>
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Price</label>
            <input class="form-control form-control-sm" value="PKR {{ number_format((float) Cart::subtotal(), 2) }}"
                readonly>
        </div>
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Tax</label>
            <input class="form-control form-control-sm" value="PKR {{ number_format((float) Cart::tax(), 2) }}"
                readonly>
        </div>
        <div class="col-md-3 col-6 form-group mb-2">
            <label>Discount</label>
            <input class="form-control form-control-sm"
                value="PKR {{ number_format($productItem->sum(function ($item) {return (float) ($item->options->discount ?? 0) * $item->qty;}),2) }}"
                readonly>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
        <span class="font-weight-bold">Grand Total</span>
        <span class="font-weight-bold text-primary" id="cart-total">PKR
            {{ number_format((float) Cart::total(), 2) }}</span>
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
            <span class="font-weight-bold text-success" id="change_amount">PKR 0.00</span>
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

<style>
    .pos-cart-table-wrap {
        overflow-x: auto;
    }

    .pos-cart-table {
        min-width: 760px;
        font-size: .68rem;
    }

    .pos-cart-table th {
        background: #fafbfd;
        border-top: 0;
        color: #273142;
        font-weight: 700;
        padding: .55rem .4rem;
        white-space: nowrap;
    }

    .pos-cart-table td {
        border-top: 1px solid #edf0f4;
        color: #536071;
        padding: .45rem .4rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .pos-cart-product-image {
        height: 30px;
        width: 38px;
        object-fit: contain;
    }

    .pos-cart-product-name {
        color: #273142 !important;
        font-weight: 600;
        max-width: 115px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-quantity-control {
        align-items: center;
        display: flex;
        gap: .25rem;
    }

    .pos-quantity-control span {
        min-width: 25px;
        text-align: center;
    }

    .pos-quantity-button {
        background: #fff;
        border: 1px solid #b9d5ff;
        border-radius: 3px;
        color: #2f80ed;
        height: 27px;
        line-height: 20px;
        padding: 0;
        width: 27px;
    }

    .pos-quantity-add {
        background: #2f80ed;
        color: #fff;
    }

    .pos-cart-summary label {
        color: #273142;
        display: block;
        font-size: .68rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .pos-cart-summary .form-control {
        border-color: #e1e6ed;
        color: #8490a0;
        font-size: .7rem;
    }

    .pos-discount-input {
        max-width: 68px;
        min-width: 58px;
    }

    .pos-discount-control {
        align-items: center;
        display: flex;
        gap: .25rem;
    }

    .pos-discount-apply {
        font-size: .62rem;
        padding: .2rem .35rem;
    }
</style>
