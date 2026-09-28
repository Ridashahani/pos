@extends('dashboard.body.main')

@section('container')
<style>
    .pp-card {
        background: #fff;
        border: 1px solid #edf0f4;
        border-radius: 6px;
    }

    .pp-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf0f4;
    }

    .pp-card-header h5 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .pp-label {
        font-size: .75rem;
        font-weight: 700;
        color: #273142;
        margin-bottom: .35rem;
        display: block;
    }

    .pp-add-btn {
        width: 46px;
        height: 44px;
        border-radius: 10px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .pp-cart-wrap {
        height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        position: relative;
    }

    .pp-cart-table {
        width: 100%;
        table-layout: fixed;
        font-size: 11px;
    }

    .pp-cart-table th {
        background: #fafbfd;
        border-top: 0;
        color: #273142;
        font-weight: 900;
        padding: .55rem .4rem;
        white-space: nowrap;
    }

    .pp-cart-table td {
        border-top: 1px solid #edf0f4;
        color: #536071;
        padding: .45rem .4rem;
        vertical-align: middle;
    }

    .pp-cart-table td.pp-name {
        color: #273142;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pp-cart-table td.pp-name small {
        display: block;
        font-size: .7rem;
        color: #8490a0;
        font-weight: 400;
    }

    .pp-cart-table .form-control {
        font-size: .75rem;
        padding: .25rem .35rem;
        height: 30px;
    }

    .pp-cart-table .quantity-input {
        text-align: center;
    }

    .pp-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #8490a0;
    }

    .pp-summary {
        padding: 1rem 1.25rem;
        border-top: 1px solid #edf0f4;
    }

    .pp-summary .form-control {
        font-size: .75rem;
        background: #e9ecef;
        color: #8490a0;
    }

    .pp-product-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: .75rem;
        max-height: 430px;
        overflow-y: auto;
        padding: 2px;
    }

    .pp-product {
        border: 1px solid #edf0f4;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        padding: .6rem;
        transition: all .15s;
    }

    .pp-product:hover {
        border-color: #b9d5ff;
        box-shadow: 0 2px 8px rgba(47, 128, 237, .12);
    }

    .pp-product.active {
        border-color: #2f80ed;
        background: #eaf2ff;
        box-shadow: 0 0 0 2px rgba(47, 128, 237, .25);
    }

    .pp-product img {
        width: 100%;
        height: 80px;
        object-fit: contain;
        margin-bottom: .4rem;
    }

    .pp-product .pp-p-name {
        font-weight: 600;
        font-size: .78rem;
        color: #273142;
        line-height: 1.2;
        height: 2.4em;
        overflow: hidden;
    }

    .pp-product .pp-p-price {
        font-size: .75rem;
        color: #2f80ed;
        font-weight: 700;
        margin-top: .25rem;
    }

    #no-match {
        grid-column: 1/-1;
    }

    .pp-picker {
        background: #f5f8fb;
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        padding: .9rem;
        margin-top: 1rem;
    }

    .supplier-modal-backdrop {
        align-items: center;
        background: rgba(15, 23, 42, .45);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 1rem;
        position: fixed;
        z-index: 1050;
    }

    .supplier-modal-backdrop.is-open {
        display: flex;
    }

    .supplier-modal {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 15px 40px rgba(15, 23, 42, .2);
        max-width: 460px;
        width: 100%;
    }

    .supplier-modal-header {
        border-bottom: 1px solid #e5eaf0;
        color: #364152;
        font-size: .95rem;
        font-weight: 700;
        padding: 1rem 1.1rem;
    }

    .supplier-modal-body {
        padding: 1.1rem;
    }

    .supplier-modal-close {
        background: transparent;
        border: 0;
        color: #8490a0;
        font-size: 1.4rem;
        line-height: 1;
    }

    .pp-multi {
        position: relative;
    }

    .pp-multi-btn {
        text-align: left;
        background: #fff;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        height: 34px;
        padding: 0 .6rem;
        line-height: 34px;
        font-size: .8rem;
        display: block;
        width: 100%;
    }

    .pp-multi-menu {
        display: none;
        position: absolute;
        left: 0;
        right: 0;
        z-index: 20;
        background: #fff;
        border: 1px solid #dfe5ec;
        border-radius: 6px;
        box-shadow: 0 6px 18px rgba(37, 52, 72, .12);
        max-height: 200px;
        overflow-y: auto;
        padding: .4rem .7rem;
    }

    .pp-multi.open .pp-multi-menu {
        display: block;
    }

    .pp-check {
        display: flex;
        align-items: center;
        font-size: .78rem;
        margin: 0;
        padding: .2rem 0;
        cursor: pointer;
    }

    .pp-check input {
        margin-right: .5rem;
    }

    .pp-group {
        font-size: .7rem;
        font-weight: 700;
        color: #8490a0;
        text-transform: uppercase;
        margin-top: .3rem;
    }
</style>

<div class="container-fluid">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row" id="purchase-view">
        {{-- LEFT: Current Purchase --}}
        <div class="col-lg-7 mb-4">
            <form id="purchase-form" method="POST" action="{{ route('purchases.store') }}">
                @csrf
                <div class="pp-card">
                    <div class="pp-card-header">
                        <h5>
                            <x-heroicon-o-shopping-cart class="w-5 h-5 mr-2" /> Current Purchase
                        </h5>
                        <div class="d-flex align-items-center">
                            <span id="items-badge" class="badge badge-secondary mr-3">0 Items</span>
                            <a href="{{ route('purchases.index') }}" class="btn btn-light btn-sm border">Back</a>
                        </div>
                    </div>

                    {{-- Supplier / Branch / Date --}}
                    <div class="p-3 px-4 border-bottom">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="pp-label" for="supplier">Supplier <span
                                        class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <select id="supplier" name="supplier_id" class="form-control mr-2" required>
                                        <option value="">Select Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="open-supplier-modal" class="btn btn-info p-add-btn"
                                        aria-label="Add supplier">
                                        <x-heroicon-o-plus class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="pp-label" for="branch">Branch <span class="text-danger">*</span></label>
                                <select id="branch" name="branch_id" class="form-control" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-0">
                                <label class="pp-label" for="purchase-date">Date <span
                                        class="text-danger">*</span></label>
                                <input id="purchase-date" name="purchase_date" type="date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- Items table --}}
                    <div class="pp-cart-wrap">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 pp-cart-table" id="items-table" style="display:none;">
                                <thead>
                                    <tr>
                                        <th style="width:6%">#</th>
                                        <th style="width:30%">Product</th>
                                        <th style="width:16%">Qty</th>
                                        <th style="width:22%">Unit Price</th>
                                        <th style="width:20%">Sub Total</th>
                                        <th style="width:6%"></th>
                                    </tr>
                                </thead>
                                <tbody id="purchase-items"></tbody>
                            </table>
                        </div>
                        <div id="empty-state" class="pp-empty">
                            <div class="bg-light rounded-circle p-3 mb-3">
                                <x-heroicon-o-shopping-bag class="w-8 h-8 text-secondary" />
                            </div>
                            <p class="mb-0 font-weight-medium">Add at least one product</p>
                            <small>Select a product from the right panel</small>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="pp-summary">
                        <div class="row">
                            <div class="col-md-4 col-6 form-group mb-2">
                                <label class="pp-label">Item</label>
                                <input id="sum-items" class="form-control form-control-sm" value="0" readonly>
                            </div>
                            <div class="col-md-4 col-6 form-group mb-2">
                                <label class="pp-label">Total</label>
                                <input id="sum-total" class="form-control form-control-sm" value="PKR 0.00" readonly>
                            </div>
                            <div class="col-md-4 col-12 form-group mb-2">
                                <label class="pp-label">Due</label>
                                <input id="sum-due" class="form-control form-control-sm" value="PKR 0.00" readonly>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
                            <span class="font-weight-bold">Grand Total</span>
                            <span class="font-weight-bold text-primary" id="total-amount">PKR 0.00</span>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="p-3 px-4 bg-white border-top">
                        <div class="row">
                            <div class="col-6 pr-1">
                                <label class="pp-label text-muted">Payment Status</label>
                                <select id="payment-status" name="payment_status" class="form-control form-control-sm"
                                    required>
                                    <option value="paid">Paid</option>
                                    <option value="due">Due</option>
                                </select>
                            </div>
                            <div class="col-6 pl-1">
                                <label class="pp-label text-muted">Amount Paid</label>
                                <input id="amount-paid" name="paid_amount" type="number"
                                    class="form-control form-control-sm" value="0" min="0"
                                    step="0.01">
                            </div>
                        </div>
                        <button type="submit"
                            class="btn btn-primary btn-lg btn-block rounded-pill shadow-lg d-flex align-items-center justify-content-center my-3 px-2 py-2">
                            <span class="mr-2">Save Purchase</span>
                            <x-heroicon-o-arrow-right class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- RIGHT: Product panel --}}
        <div class="col-lg-5">
            <div class="pp-card p-3">
                <div class="input-group mb-3">
                    <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                    <div class="input-group-append">
                        <span class="input-group-text bg-info text-white border-info">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </span>
                    </div>
                </div>

                <div class="pp-product-list" id="product-list">
                    @forelse ($products as $product)
                    <div class="pp-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                        data-price="{{ $product->buying_price }}">
                        <img src="{{ $product->image ? asset('assets/images/product/' . $product->image) : asset('assets/images/product/default.webp') }}"
                            alt="{{ $product->name }}">
                        <div class="pp-p-name">{{ $product->name }}</div>
                        <div class="pp-p-price">PKR {{ number_format($product->buying_price, 2) }}</div>
                    </div>
                    @empty
                    <div class="alert alert-info mb-0 text-center" style="grid-column:1/-1;">No products found.
                    </div>
                    @endforelse
                    <div id="no-match" class="alert alert-info mb-0 text-center" style="display:none;">No products
                        found.</div>
                </div>

                <div class="pp-picker">
                    <div class="mb-2" style="font-size:.8rem;">
                        Selected: <strong id="selected-product-name" class="text-primary"> None </strong>
                    </div>
                    @php
                    $variationData = $variations
                    ->map(function ($variation) {
                    $types = collect($variation->types ?? [])
                    ->map(
                    fn($t) => is_array($t)
                    ? $t['value'] ?? ($t['name'] ?? '')
                    : (is_object($t)
                    ? $t->value ?? ($t->name ?? '')
                    : $t),
                    )
                    ->filter(fn($t) => $t !== '' && $t !== null)
                    ->values()
                    ->all();

                    return ['id' => $variation->id, 'name' => $variation->name, 'types' => $types];
                    })
                    ->filter(fn($v) => count($v['types']) > 0)
                    ->values();
                    @endphp

                    <div class="form-group mb-2">
                        <label class="pp-label">Variation</label>
                        <div class="pp-multi" id="variation-dd">
                            <button type="button" id="variation-btn"
                                class="form-control form-control-sm pp-multi-btn">Select Variation</button>
                            <div class="pp-multi-menu" id="variation-menu">
                                @foreach ($variationData as $v)
                                <label class="pp-check">
                                    <input type="checkbox" class="variation-check" value="{{ $v['id'] }}">
                                    {{ $v['name'] }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="pp-label">Value</label>
                        <div class="pp-multi" id="value-dd">
                            <button type="button" id="value-btn"
                                class="form-control form-control-sm pp-multi-btn">Select Value</button>
                            <div class="pp-multi-menu" id="value-menu">
                                <div class="text-muted" style="font-size:.75rem;">Select variation first</div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-item-btn" class="btn btn-primary btn-sm btn-block">Add to
                        Purchase</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="supplier-page" style="display:none;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Add Supplier</h4>
                        <a href="{{ route('units.index') }}" class="btn btn-light btn-sm">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('suppliers.form', ['inModal' => true])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const items = document.getElementById('purchase-items');
        const itemsTable = document.getElementById('items-table');
        const emptyState = document.getElementById('empty-state');
        const itemsBadge = document.getElementById('items-badge');
        const totalAmount = document.getElementById('total-amount');
        const sumItems = document.getElementById('sum-items');
        const sumTotal = document.getElementById('sum-total');
        const sumDue = document.getElementById('sum-due');
        const amountPaid = document.getElementById('amount-paid');

        const productList = document.getElementById('product-list');
        const productSearch = document.getElementById('product-search');
        const noMatch = document.getElementById('no-match');
        const selectedName = document.getElementById('selected-product-name');
        const addBtn = document.getElementById('add-item-btn');

        const supplierSelect = document.getElementById('supplier');
        const supplierPage = document.getElementById('supplier-page');
        const purchaseView = document.getElementById('purchase-view');

        let rowCounter = 0;
        let selectedProduct = null;

        const esc = s => String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));

        // ---- Product search & select ----
        productSearch.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            productList.querySelectorAll('.pp-product').forEach(el => {
                const show = el.dataset.name.toLowerCase().includes(q);
                el.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            noMatch.style.display = visible ? 'none' : '';
        });
        productSearch.addEventListener('keydown', e => {
            if (e.key === 'Enter') e.preventDefault();
        });

        productList.addEventListener('click', function(e) {
            const card = e.target.closest('.pp-product');
            if (!card) return;
            productList.querySelectorAll('.pp-product').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            selectedProduct = {
                id: card.dataset.id,
                name: card.dataset.name,
                price: Number(card.dataset.price || 0)
            };
            selectedName.textContent = selectedProduct.name;
        });

        // ---- Variation + Value picker (both multi-select) ----
        const variationTypes = @json($variationData);
        const varDd = document.getElementById('variation-dd');
        const valDd = document.getElementById('value-dd');
        const varBtn = document.getElementById('variation-btn');
        const valBtn = document.getElementById('value-btn');
        const varMenu = document.getElementById('variation-menu');
        const valMenu = document.getElementById('value-menu');

        const pickedVars = new Set();
        const pickedVals = new Set();

        const findVar = id => variationTypes.find(v => String(v.id) === String(id));

        [
            [varDd, varBtn],
            [valDd, valBtn]
        ].forEach(([dd, btn]) => {
            btn.addEventListener('click', () => dd.classList.toggle('open'));
        });
        document.addEventListener('click', e => {
            [varDd, valDd].forEach(dd => {
                if (!dd.contains(e.target)) dd.classList.remove('open');
            });
        });

        function updateButtons() {
            const names = [...pickedVars].map(id => findVar(id)?.name).filter(Boolean);
            varBtn.textContent = names.length ? names.join(', ') : 'Select Variation';

            const vals = [...pickedVals].map(k => k.split('||')[1]);
            valBtn.textContent = vals.length ? vals.join(', ') : 'Select Value';
        }

        function renderValues() {
            // drop values whose variation was unticked
            [...pickedVals].forEach(k => {
                if (!pickedVars.has(k.split('||')[0])) pickedVals.delete(k);
            });

            if (!pickedVars.size) {
                valMenu.innerHTML =
                    '<div class="text-muted" style="font-size:.75rem;">Select variation first</div>';
            } else {
                valMenu.innerHTML = [...pickedVars].map(id => {
                    const v = findVar(id);
                    return `<div class="pp-group">${esc(v.name)}</div>` + v.types.map(t => `
                        <label class="pp-check">
                            <input type="checkbox" class="value-check" data-id="${esc(id)}" data-value="${esc(t)}"
                                ${pickedVals.has(id + '||' + t) ? 'checked' : ''}> ${esc(t)}
                        </label>`).join('');
                }).join('');
            }
            updateButtons();
        }

        varMenu.addEventListener('change', function(e) {
            const cb = e.target.closest('.variation-check');
            if (!cb) return;
            cb.checked ? pickedVars.add(cb.value) : pickedVars.delete(cb.value);
            renderValues();
        });

        valMenu.addEventListener('change', function(e) {
            const cb = e.target.closest('.value-check');
            if (!cb) return;
            const key = cb.dataset.id + '||' + cb.dataset.value;
            cb.checked ? pickedVals.add(key) : pickedVals.delete(key);
            updateButtons();
        });

        addBtn.addEventListener('click', function() {
            if (!selectedProduct) {
                alert('Select Product first.');
                return;
            }

            const groups = [];
            for (const id of pickedVars) {
                const v = findVar(id);
                const opts = [...pickedVals]
                    .filter(k => k.split('||')[0] === String(id))
                    .map(k => ({
                        variation_id: id,
                        name: v.name,
                        value: k.split('||')[1]
                    }));
                if (!opts.length) {
                    alert(`${v.name} ki value select karein.`);
                    return;
                }
                groups.push(opts);
            }

            const combos = groups.reduce((acc, g) => acc.flatMap(a => g.map(x => [...a, x])), [
                []
            ]);
            combos.forEach(c => {
                const label = c.length ? c.map(v => `${v.name}: ${v.value}`).join(', ') :
                    'Standard';
                addRow(selectedProduct, label, c);
            });

            // reset picker
            pickedVars.clear();
            pickedVals.clear();
            varMenu.querySelectorAll('.variation-check').forEach(cb => cb.checked = false);
            renderValues();
        });

        // ---- Rows ----
        function addRow(product, label, variations) {
            const rowId = rowCounter++;
            const row = document.createElement('tr');
            row.className = 'purchase-item';

            let varInputs = '';
            variations.forEach((v, i) => {
                varInputs += `
                    <input type="hidden" name="items[${rowId}][variations][${i}][variation_id]" value="${esc(v.variation_id)}">
                    <input type="hidden" name="items[${rowId}][variations][${i}][name]" value="${esc(v.name)}">
                    <input type="hidden" name="items[${rowId}][variations][${i}][value]" value="${esc(v.value)}">`;
            });

            row.innerHTML = `
                <td class="row-no"></td>
                <td class="pp-name" title="${esc(product.name)} — ${esc(label)}">
                    ${esc(product.name)}<small>${esc(label)}</small>
                </td>
                <td><input type="number" class="form-control quantity-input" name="items[${rowId}][quantity]" value="1" min="1"></td>
                <td><input type="number" class="form-control cost-input" name="items[${rowId}][unit_price]" value="${product.price.toFixed(2)}" min="0" step="0.01"></td>
                <td class="amount-cell">PKR ${product.price.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-link text-danger p-0 remove-product" title="Remove product">&times;</button>
                    <input type="hidden" name="items[${rowId}][product_id]" value="${esc(product.id)}">
                    ${varInputs}
                </td>`;
            items.appendChild(row);
            refresh();
        }

        function refresh() {
            const rows = items.querySelectorAll('.purchase-item');
            let total = 0,
                qtySum = 0;

            rows.forEach((row, index) => {
                row.querySelector('.row-no').textContent = index + 1;
                const q = Math.max(0, Number(row.querySelector('.quantity-input').value) || 0);
                const p = Math.max(0, Number(row.querySelector('.cost-input').value) || 0);
                const amt = q * p;
                row.querySelector('.amount-cell').textContent = `PKR ${amt.toFixed(2)}`;
                total += amt;
                qtySum += q;
            });

            const paid = Math.max(0, Number(amountPaid.value) || 0);
            const due = Math.max(0, total - paid);

            itemsTable.style.display = rows.length ? '' : 'none';
            emptyState.style.display = rows.length ? 'none' : '';
            itemsBadge.textContent = `${rows.length} Items`;
            sumItems.value = qtySum;
            sumTotal.value = `PKR ${total.toFixed(2)}`;
            sumDue.value = `PKR ${due.toFixed(2)}`;
            totalAmount.textContent = `PKR ${total.toFixed(2)}`;
        }

        amountPaid.addEventListener('input', refresh);
        items.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity-input') || e.target.classList.contains(
                    'cost-input')) refresh();
        });
        items.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-product');
            if (!btn) return;
            btn.closest('.purchase-item').remove();
            refresh();
        });

        document.getElementById('purchase-form').addEventListener('submit', function(e) {
            if (!items.querySelector('.purchase-item')) {
                e.preventDefault();
                alert('Please add at least one product.');
            }
        });

        // ---- Add Supplier page-view (AJAX save) ----
        const supplierForm = document.getElementById('supplier-form');
        const supplierErrors = document.getElementById('supplier-errors');
        const saveBtn = document.getElementById('save-supplier');

        const closeModal = () => {
            supplierPage.style.display = 'none';
            purchaseView.style.display = '';
            supplierErrors.style.display = 'none';
        };
        document.getElementById('open-supplier-modal').addEventListener('click', () => {
            purchaseView.style.display = 'none';
            supplierPage.style.display = '';
            window.scrollTo({
                top: 0
            });
        });
        document.getElementById('cancel-supplier').addEventListener('click', closeModal);

        supplierForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            supplierErrors.style.display = 'none';
            saveBtn.disabled = true;

            try {
                const res = await fetch(@json(route('suppliers.store')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ?.content ||
                            document.querySelector('#purchase-form input[name="_token"]')
                            .value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(supplierForm)
                });
                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    const msgs = Object.values(data.errors || {}).flat();
                    supplierErrors.innerHTML = msgs.map(m => `<div>${esc(m)}</div>`).join('');
                    supplierErrors.style.display = '';
                    return;
                }
                if (!res.ok || !data.id) throw new Error('Request failed');

                supplierSelect.add(new Option(data.name, data.id, true, true));
                supplierForm.reset();
                closeModal();
            } catch (err) {
                supplierErrors.textContent = 'Supplier could not be saved. Please try again.';
                supplierErrors.style.display = '';
            } finally {
                saveBtn.disabled = false;
            }
        });

        refresh();
    });
</script>
@endsection