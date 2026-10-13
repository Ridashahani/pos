@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <!-- Success Alert -->
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row pos-shell">
            <!-- FULL WIDTH: Current Sale -->
            <div class="col-12 pos-cart">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between p-3">
                        <h5 class="mb-0 text-white">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 mr-1 inline" /> Current Sale
                        </h5>
                        <span class="badge badge-light text-primary font-weight-bold" id="cart-count-badge">
                            {{ Cart::count() }} items
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="pos-order-fields p-3 border-bottom">
                            <div class="row">
                                <div class="col-md-6 col-lg-3 form-group mb-2">
                                    <label class="pos-field-label">Customer</label>
                                    <div class="input-group">
                                        <select class="form-control select2" id="customer_id" name="customer_id"
                                            style="width: 82%;">
                                            <option value="" selected disabled>Select Customer</option>
                                        </select>
                                        <div class="input-group-append" style="width: 18%;">

                                            <a href="{{ route('customers.create') }}">
                                                <button type="button" class="btn btn-primary btn-block ml-2"
                                                    title="Add New Customer">
                                                    <x-heroicon-o-plus class="w-3 h-5" />
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 form-group mb-2">
                                    <label class="pos-field-label">Biller</label>
                                    <select class="form-control">
                                        <option>Admin</option>
                                        <option>Manager</option>
                                        <option>Cashier</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-3 form-group mb-2">
                                    <label class="pos-field-label" for="branch_id">Branch</label>
                                    <select class="form-control" id="branch_id" name="branch_id" @if ($branches->isEmpty()) disabled @else required @endif>
                                        @if ($branches->isEmpty())
                                            <option value="" selected>No active branches available</option>
                                        @else
                                            <option value="" disabled @if (!$selectedBranchId) selected @endif>Select a branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" @if ((string) $selectedBranchId === (string) $branch->id) selected @endif>{{ $branch->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if ($branches->isEmpty())
                                        <small class="form-text text-danger">Create an active branch and assign it to your account before making a sale.</small>
                                    @endif
                                </div>
                                <div class="col-md-6 col-lg-3 form-group mb-2">
                                    <label class="pos-field-label">Reference No</label>
                                    <input class="form-control" type="text" placeholder="Reference No">
                                </div>
                            </div>
                        </div>

                        <!-- Searchable product dropdown.
                             Products are fetched on demand (30 per page, scroll for more),
                             so no product list is rendered into this page. -->
                        <div class="pos-product-picker p-3 border-bottom">
                            <label class="pos-field-label" for="product_multiselect">Products</label>
                            <select id="product_multiselect" class="form-control" style="width: 100%;">
                                <option></option>
                            </select>
                            <small class="form-text text-muted">
                                Click the field, type to search by name or code, then pick a product to add it to the
                                sale. Change quantities or remove products in the table below.
                            </small>
                        </div>

                        <!-- Dynamic Cart Sidebar -->
                        <div id="cart-sidebar-container">
                            @include('pos.cart-sidebar')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white"
                    style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="modal-title font-weight-bold mx-auto">Complete Payment</h5>

                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="submitOrder(event)">
                    @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" id="modal_customer_id" name="customer_id" required>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted font-weight-bold">Total Bill:</td>
                                    <td class="text-right font-weight-bold h5 text-primary" id="modal_total_display">
                                        @php
                                            $paymentItems = Cart::content();
                                            $paymentSubtotal = $paymentItems->sum(fn ($item) => (float) ($item->options->original_price ?? $item->price) * $item->qty);
                                            $paymentDiscount = $paymentItems->sum(fn ($item) => (float) ($item->options->discount ?? 0) * $item->qty);
                                            $paymentTax = $paymentItems->sum(fn ($item) => (float) ($item->options->tax ?? 0) * $item->qty);
                                        @endphp
                                        {{ number_format(max(0, $paymentSubtotal - $paymentDiscount + $paymentTax), 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold">Paid With:</td>
                                    <td class="text-right font-weight-bold" id="modal_payment_method">Cash</td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold">Amount Paid:</td>
                                    <td class="text-right font-weight-bold h5 text-success" id="modal_pay_amount">0.00
                                    </td>
                                </tr>
                                <tr class="border-top" id="modal_due_row">
                                    <td class="text-muted font-weight-bold">Due Amount:</td>
                                    <td class="text-right font-weight-bold h5 text-danger" id="modal_due_amount">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <!-- Modal Actions -->
                    <div class="modal-footer border-top-0 d-flex justify-content-between p-4 bg-light"
                        style="border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                        <button type="button" class="btn btn-cancel px-4" data-dismiss="modal">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Add New Customer</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- AJAX Form Submission -->
                <form onsubmit="storeCustomer(event)">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" name="city">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-save">
                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('specificpagescripts')
    <!-- External Dependencies: Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // DOMContentLoaded (not "load"): "load" waits for every image on the page,
        // which delayed the dropdown from becoming usable.
        document.addEventListener('DOMContentLoaded', function() {
            // Customer search (AJAX)
            $('#customer_id').select2({
                placeholder: " Select Customer",
                allowClear: true,
                width: 'resolve',
                ajax: {
                    url: "{{ route('pos.customers.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });

            // Product dropdown: search bar first, products below it.
            // Server-side search + pagination (30 per page, scroll for more): the browser
            // never builds more than a screenful of rows, whatever the catalogue size.
            const $products = $('#product_multiselect');

            $products.select2({
                placeholder: 'Search and select a product...',
                width: '100%',
                minimumResultsForSearch: 0,     // always show the search bar
                ajax: {
                    url: "{{ route('pos.products.search') }}",
                    dataType: 'json',
                    delay: 200,                 // debounce typing
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        };
                    }
                    // the endpoint already returns { results: [...], pagination: { more } }
                },
                templateResult: function(option) {
                    if (option.loading || !option.id) return option.text;
                    const $row = $('<span class="pos-opt"></span>');
                    $row.append($('<img class="pos-opt-thumb" alt="" loading="lazy" decoding="async">')
                        .attr('src', option.image));
                    const $text = $('<span class="pos-opt-text"></span>');
                    $text.append($('<span class="pos-opt-name"></span>').text(option.name));
                    const meta = [
                        option.code,
                        Number(option.price).toLocaleString('en-US'),
                        'Stock: ' + option.stock
                    ].filter(Boolean).join('  |  ');
                    $text.append($('<small class="pos-opt-meta"></small>').text(meta));
                    $row.append($text);
                    return $row;
                },
                templateSelection: function(option) {
                    return option.name || option.text;
                }
            });

            // Select2 4.1.0-rc.0 + jQuery 3.6+ does not focus the search box on open,
            // so focus it ourselves and the cashier can type straight away.
            $(document).on('select2:open', function() {
                setTimeout(function() {
                    const field = document.querySelector('.select2-search--dropdown .select2-search__field');
                    if (field) field.focus();
                }, 0);
            });

            // Picking a product adds it to the cart. Only the id is sent: the server
            // reads name / price / image from the database.
            $products.on('select2:select', function(e) {
                // Reset the picker right away so the same product can be picked again.
                setTimeout(window.syncProductSelect, 0);
                window.posAddProduct({ id: e.params.data.id });
            });
        });

        // The picker is only an "add" control: it is cleared after every pick.
        // The cart table below is the source of truth for what is in the sale.
        window.syncProductSelect = function() {
            const $products = $('#product_multiselect');
            if (!$products.length || !$products.data('select2')) return;
            $products.val(null).trigger('change.select2');
        };

        /* =====================================================================
         * CART ENGINE
         * - spinner arrows / typing update the numbers instantly (local maths)
         * - saves are debounced and sent ONE AT A TIME (no lost clicks)
         * - qty / tax / discount saves get a tiny JSON reply (no HTML re-render)
         * - the cart HTML is only redrawn when rows are added or removed
         * ===================================================================== */
        (function () {
            const container = document.getElementById('cart-sidebar-container');
            const badge = document.getElementById('cart-count-badge');
            const CSRF = '{{ csrf_token() }}';
            const FIELDS = {
                qty:      { sel: '.pos-quantity-input', url: "{{ url('pos/update') }}/",   key: 'qty' },
                discount: { sel: '.pos-discount-input', url: "{{ url('pos/discount') }}/", key: 'discount' },
                rate:     { sel: '.pos-tax-rate-input', url: "{{ url('pos/tax-rate') }}/", key: 'tax_rate' },
            };
            const ALL_FIELDS = Object.values(FIELDS).map(f => f.sel).join(',');
            const fmt = n => new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(n);
            const num = v => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };
            const rows = () => [...container.querySelectorAll('tr[data-item-id]')];
            const rowFor = id => rows().find(tr => tr.dataset.itemId === String(id));
            const fieldOf = input => Object.keys(FIELDS).find(k => input.matches(FIELDS[k].sel));
            const isField = el => el instanceof Element && el.matches(ALL_FIELDS);

            /* ---------- instant local calculation ---------- */
            function recalcRow(tr) {
                const qtyIn = tr.querySelector(FIELDS.qty.sel);
                const max = qtyIn.max ? parseInt(qtyIn.max, 10) : Infinity;
                const qty = Math.min(max, Math.max(0, parseInt(qtyIn.value, 10) || 0));
                const rate = Math.min(100, Math.max(0, num(tr.querySelector(FIELDS.rate.sel).value)));
                const discIn = tr.querySelector(FIELDS.discount.sel);
                const unit = num(tr.dataset.unitPrice);
                const gross = unit * qty;
                const tax = gross * rate / 100;
                const incl = gross + tax;
                const discount = Math.min(Math.max(0, num(discIn.value)), incl);
                const net = incl - discount;

                tr.querySelector('.pos-gross-amount').textContent = fmt(gross);
                tr.querySelector('.pos-tax-amount').textContent = fmt(tax);
                tr.querySelector('.pos-incl-tax-amount').textContent = fmt(incl);
                tr.querySelector('.pos-net-amount').textContent = tr.dataset.currency + ' ' + fmt(net);
                discIn.max = incl.toFixed(2);
                return { qty, unit, tax, discount, net, currency: tr.dataset.currency };
            }

            function recalcSummary() {
                const t = { items: 0, price: 0, tax: 0, discount: 0, net: 0 };
                let currency = 'PKR';
                rows().forEach(tr => {
                    const c = recalcRow(tr);
                    t.items += c.qty;
                    t.price += c.unit * c.qty;
                    t.tax += c.tax;
                    t.discount += c.discount;
                    t.net += c.net;
                    currency = c.currency;
                });
                const set = (sel, v) => { const el = container.querySelector(sel); if (el) el.value = v; };
                set('.pos-summary-items', t.items);
                set('.pos-summary-price', currency + ' ' + fmt(t.price));
                set('.pos-summary-tax', currency + ' ' + fmt(t.tax));
                set('.pos-summary-discount', currency + ' ' + fmt(t.discount));
                // const footerRate = container.querySelector('.pos-footer-tax-rate');
                // if (footerRate) footerRate.textContent = fmt(t.price > 0 ? (t.tax / t.price) * 100 : 0) + '%';
                const footerDiscount = container.querySelector('.pos-footer-discount');
                if (footerDiscount) footerDiscount.textContent = currency + ' ' + fmt(t.discount);
                const total = document.getElementById('cart-total');
                if (total) {
                    total.dataset.total = t.net;
                    total.textContent = currency + ' ' + fmt(t.net);
                }
                badge.innerText = t.items + ' items';
                if (typeof calculateChange === 'function') calculateChange();
            }

            function normalize(input) {
                const field = fieldOf(input);
                if (field === 'qty') {
                    const max = input.max ? parseInt(input.max, 10) : Infinity;
                    input.value = Math.min(max, Math.max(0, parseInt(input.value, 10) || 0));
                } else if (field === 'rate') {
                    input.value = Math.min(100, Math.max(0, num(input.value)));
                } else if (field === 'discount') {
                    input.value = recalcRow(input.closest('tr')).discount;
                }
                recalcSummary();
            }

            /* ---------- one request at a time ---------- */
            let chain = Promise.resolve();
            const timers = new Map();

            function enqueue(task) {
                chain = chain.then(task).catch(err => console.error(err));
                return chain;
            }

            function schedule(tr, field, delay = 400) {
                const id = tr.dataset.itemId;
                const key = id + '|' + field;
                clearTimeout(timers.get(key)?.t);
                const run = () => { timers.delete(key); enqueue(() => saveField(id, field)); };
                timers.set(key, { t: setTimeout(run, delay), run });
            }

            function flushTimers() {
                [...timers.values()].forEach(x => { clearTimeout(x.t); x.run(); });
            }

            async function saveField(itemId, field) {
                const tr = rowFor(itemId);
                if (!tr) return;
                const f = FIELDS[field];
                const input = tr.querySelector(f.sel);
                const focused = document.activeElement === input;

                // Still typing / still holding the spinner: wait for the final value.
                if (focused && (input.value === '' || (field === 'qty' && num(input.value) === 0))) return;

                normalize(input);
                if (num(input.value) === num(input.dataset.saved)) return;

                const value = input.value;
                const res = await fetch(f.url + tr.dataset.rowId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ [f.key]: value }),
                });
                const data = await res.json().catch(() => null);

                if (res.ok && data?.success) {
                    input.dataset.saved = value;
                    if (data.cart_html) {
                        render(data);                      // a line was removed: redraw once
                    } else if (data.row_id) {
                        tr.dataset.rowId = data.row_id;    // row id changes when options change
                    }
                } else if (data?.cart_html) {
                    render(data);                          // e.g. 409: row vanished, show the real cart
                } else {
                    input.value = input.dataset.saved;     // validation/server error: roll back
                    recalcSummary();
                }
            }

            /* ---------- rendering (only when rows are added / removed) ---------- */
            const KEEP = ['pay_amount', 'payment_type'];

            function render(data) {
                const wrap = container.querySelector('.cart-items-wrapper');
                const top = wrap ? wrap.scrollTop : 0;
                const kept = KEEP.map(id => [id, document.getElementById(id)?.value]);

                container.innerHTML = data.cart_html;
                badge.innerText = data.cart_count + ' items';

                const newWrap = container.querySelector('.cart-items-wrapper');
                if (newWrap) newWrap.scrollTop = top;
                kept.forEach(([id, v]) => {
                    const el = document.getElementById(id);
                    if (el && v !== undefined) el.value = v;
                });
                window.syncProductSelect();       // keep the product picker reset
            }

            /* ---------- native spinner arrows: don't leave the input "active" ---------- */
            let spinnerPointer = null;

            document.addEventListener('pointerdown', function (event) {
                if (!(event.target instanceof Element)) return;
                const input = event.target.closest(ALL_FIELDS);
                if (!input || event.button !== 0 || !container.contains(input)) {
                    spinnerPointer = null;
                    return;
                }

                const bounds = input.getBoundingClientRect();
                const spinnerWidth = 18;
                const rtl = window.getComputedStyle(input).direction === 'rtl';
                const clickedSpinner = rtl
                    ? event.clientX <= bounds.left + spinnerWidth
                    : event.clientX >= bounds.right - spinnerWidth;

                if (clickedSpinner) input.classList.add('pos-quantity-spinner-active');
                spinnerPointer = clickedSpinner ? { input, pointerId: event.pointerId } : null;
            });

            function releaseSpinner(event) {
                const sp = spinnerPointer;
                if (!sp || sp.pointerId !== event.pointerId) return;
                spinnerPointer = null;
                requestAnimationFrame(() => {
                    if (!sp.input.isConnected) return;
                    sp.input.classList.remove('pos-quantity-spinner-active');
                    if (document.activeElement === sp.input) sp.input.blur();   // focusout saves the final value
                });
            }
            document.addEventListener('pointerup', releaseSpinner);
            document.addEventListener('pointercancel', releaseSpinner);

            /* ---------- remove a line ---------- */
            function removeItem(id) {
                const tr = rowFor(id);
                if (!tr) return;
                tr.style.opacity = .4;
                flushTimers();
                enqueue(async () => {
                    const row = rowFor(id);
                    if (!row) return;
                    const res = await fetch("{{ url('pos/delete') }}/" + row.dataset.rowId, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.cart_html) render(data);
                });
            }
            window.posRemoveItem = removeItem;

            /* ---------- events (delegated, so they survive re-renders) ---------- */
            container.addEventListener('click', e => {
                const btn = e.target.closest('[data-action="remove"]');
                const tr = btn?.closest('tr[data-item-id]');
                if (!tr) return;
                removeItem(tr.dataset.itemId);
            });

            container.addEventListener('input', e => {
                if (!isField(e.target)) return;
                const field = fieldOf(e.target);
                recalcSummary();                                    // instant numbers
                if (e.target.value === '') return;                  // wait until a value is typed
                if (field === 'qty' && num(e.target.value) === 0) return;   // 0 (= remove) only on commit
                schedule(e.target.closest('tr'), field);
            });

            container.addEventListener('change', e => {
                if (!isField(e.target)) return;
                normalize(e.target);
                schedule(e.target.closest('tr'), fieldOf(e.target), 150);
            });

            container.addEventListener('keydown', e => {
                if (e.key === 'Enter' && isField(e.target)) e.target.blur();
            });

            container.addEventListener('focusout', e => {
                if (!isField(e.target)) return;
                const input = e.target;
                const tr = input.closest('tr');
                if (tr && num(input.value) !== num(input.dataset.saved)) {
                    schedule(tr, fieldOf(input), 150);
                }
            });

            /* ---------- add to cart ---------- */
            let adding = 0;

            function postAdd(formData) {
                flushTimers();
                container.classList.add('pos-busy');     // instant visual feedback
                adding++;
                return enqueue(async () => {
                    try {
                        const res = await fetch("{{ route('pos.addCart') }}", {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                            body: formData,
                        });
                        const data = await res.json();
                        if (data.success) render(data); else alert(data.message || 'Failed to add item');
                    } finally {
                        if (--adding === 0) container.classList.remove('pos-busy');
                    }
                });
            }

            // Used by the product dropdown (only the product id is sent).
            window.posAddProduct = function (p) {
                const fd = new FormData();
                fd.append('id', p.id);
                return postAdd(fd);
            };

            window.addToCart = function (event) {
                event.preventDefault();
                return postAdd(new FormData(event.target));
            };

            window.addManualItem = function () {
                const name = document.getElementById('manual_item_name').value.trim();
                const price = parseFloat(document.getElementById('manual_item_price').value);
                if (!name || isNaN(price) || price <= 0) {
                    alert('Please enter item name and valid price!');
                    return;
                }

                const fd = new FormData();
                fd.append('name', name);
                fd.append('price', price);
                fd.append('tax', parseFloat(document.getElementById('manual_item_tax').value) || 0);
                fd.append('discount', parseFloat(document.getElementById('manual_item_discount').value) || 0);
                fd.append('is_manual', 1);
                return postAdd(fd);
            };

            // Used by the payment flow: save everything pending and wait for the server.
            window.posCartIdle = function () {
                flushTimers();
                return chain;
            };
        })();

        // Logic: Create New Customer (AJAX)
        async function storeCustomer(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch("{{ route('pos.storeCustomer') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    // Append new customer to dropdown and select it
                    var text = data.customer.name + ' (' + (data.customer.phone || 'N/A') + ')';
                    var newOption = new Option(text, data.customer.id, true, true);
                    $('#customer_id').append(newOption).trigger('change');

                    // Close modal and reset form
                    $('#addCustomerModal').modal('hide');
                    form.reset();
                    alert(data.message);
                } else {
                    alert('Failed to create customer');
                }
            } catch (error) {
                console.error('Error creating customer:', error);
                alert('Error creating customer. Please check inputs.');
            }
        }

        // Logic: Real-time Change Calculation
        function calculateChange() {
            const changeElement = document.getElementById('change_amount');
            if (!changeElement) return;   // the Change row is commented out in the partial

            const totalAmount = parseFloat(document.getElementById('cart-total').dataset.total) || 0;
            const payInput = parseFloat(document.getElementById('pay_amount').value);

            if (!isNaN(payInput) && payInput >= 0) {
                const change = payInput - totalAmount;
                changeElement.innerText = change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                if (change < 0) {
                    changeElement.classList.add('text-danger');
                    changeElement.classList.remove('text-success');
                } else {
                    changeElement.classList.remove('text-danger');
                    changeElement.classList.add('text-success');
                }
            } else {
                changeElement.innerText = "0.00";
                changeElement.classList.remove('text-success', 'text-danger');
            }
        }

        // Logic: Validate Payment & Show Summary Modal
        function validateAndShowModal() {
            // 1. Ensure Customer Selected
            var customerId = $('#customer_id').val();
            if (!customerId) {
                alert("Please select a customer first!");
                return;
            }
            document.getElementById('modal_customer_id').value = customerId;

            const branchSelect = document.getElementById('branch_id');
            if (!branchSelect?.value) {
                alert(branchSelect?.options.length > 1 ? 'Please select a branch first.' : 'No active branch is available for this account.');
                return;
            }

            // 2. Validate Payment Amount
            const totalEl = document.getElementById('cart-total');
            const totalText = totalEl.innerText.trim();
            const totalAmount = parseFloat(totalEl.dataset.total) || 0;
            const payElement = document.getElementById('pay_amount');
            const payAmount = parseFloat(payElement ? payElement.value : 0);
            const method = document.getElementById('payment_type').value;

            if (isNaN(payAmount) || payAmount < 0) {
                alert('Please enter a valid amount!');
                return;
            }

            // Make sure any pending quantity/tax/discount edits are saved.
            if (window.posCartIdle) window.posCartIdle();

            // 3. Update Modal UI
            document.getElementById('modal_total_display').innerText = totalText;
            document.getElementById('modal_payment_method').innerText = method;
            document.getElementById('modal_pay_amount').innerText = payAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            // Due amount
            const due = Math.max(0, totalAmount - payAmount);
            const dueRow = document.getElementById('modal_due_row');
            const dueEl = document.getElementById('modal_due_amount');
            dueEl.innerText = due.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            dueRow.style.display = due > 0 ? '' : 'none';

            // 4. Show Modal
            $('#paymentModal').modal('show');
        }

        // Logic: Final Sale Submission (AJAX)
        async function submitOrder(event) {
            event.preventDefault();

            // Wait until every pending cart edit has reached the server.
            if (window.posCartIdle) await window.posCartIdle();

            // Construct FormData manually since inputs are in Sidebar, not in this Form
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('customer_id', document.getElementById('modal_customer_id').value);
            formData.append('branch_id', document.getElementById('branch_id').value);

            const paymentTypeElem = document.getElementById('payment_type');
            const payAmountElem = document.getElementById('pay_amount');

            if (paymentTypeElem) formData.append('payment_type', paymentTypeElem.value);
            if (payAmountElem) formData.append('pay_amount', payAmountElem.value);

            try {
                const response = await fetch("{{ route('pos.storeSale') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    $('#paymentModal').modal('hide');

                    // Allow popup for invoice
                    if (data.invoice_url) {
                        window.open(data.invoice_url, '_blank');
                    }

                    // Reset UI
                    if (data.cart_html) {
                        document.getElementById('cart-sidebar-container').innerHTML = data.cart_html;
                    }

                    // Update Cart Count Badge
                    if (data.cart_count !== undefined) {
                        document.getElementById('cart-count-badge').innerText = data.cart_count + ' items';
                    }

                    // Reset the product picker
                    window.syncProductSelect();

                    alert('Sale Successful!');

                } else {
                    alert('Sale Failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error submitting sale:', error);
                alert('An error occurred while processing the sale.');
            }
        }
    </script>

    <!-- Page Specific Styles -->
    <style>
        /* ---------- Cart table styles ---------- */
        .pos-cart-table {
            min-width: 0;
            width: 100%;
            font-size: 12px;
        }

        .pos-cart-product-name small {
            display: block;
            font-size: .70rem;
        }

        .pos-cart-table th {
            background: #fafbfd;
            border-top: 0;
            color: #273142;
            font-weight: 900;
            font-size: 11px;
            line-height: 1.15;
            padding: .4rem .25rem;
            white-space: normal;
            vertical-align: middle;
        }

        .pos-cart-table td {
            border-top: 1px solid #edf0f4;
            color: #536071;
            padding: .3rem .25rem;
            white-space: nowrap;
            vertical-align: middle;
        }

        .pos-cart-table th:first-child,
        .pos-cart-table td:first-child {
            padding-left: .5rem;
        }

        .pos-cart-table th:last-child,
        .pos-cart-table td:last-child {
            padding-right: .5rem;
        }

        .pos-cart-product-image {
            height: 30px;
            width: 38px;
            object-fit: contain;
        }

        /* The product column soaks up all spare width, so every other column
           shrinks to its content and the gaps between them disappear. */
        .pos-cart-table td.pos-cart-product-name {
            color: #273142 !important;
            font-weight: 600;
            width: 100%;
            min-width: 120px;
            max-width: 1px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-quantity-control {
            align-items: center;
            display: flex;
            gap: .25rem;
        }

        .pos-quantity-input.pos-quantity-spinner-active:focus,
        .pos-tax-rate-input.pos-quantity-spinner-active:focus,
        .pos-discount-input.pos-quantity-spinner-active:focus {
            border-color: #ced4da;
            box-shadow: none;
            outline: 0;
        }

        .pos-quantity-control span {
            min-width: 20px;
            text-align: center;
        }

        .pos-quantity-button {
            background: #fff;
            border: 1px solid #b9d5ff;
            border-radius: 3px;
            color: #2f80ed;
            height: 16px;
            line-height: 20px;
            padding: 0;
            width: 16px;
        }

        .pos-quantity-add {
            background: #2f80ed;
            color: #fff;
        }

        .pos-cart-summary label {
            color: #273142;
            display: block;
            font-size: .70rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .pos-cart-summary .form-control {
            border-color: #e1e6ed;
            color: #8490a0;
            font-size: .7rem;
        }

        .pos-discount-input {
            min-width: 50px;
            max-width: 70px;
            padding: 0.2rem 0.3rem;
        }

        .pos-cart-table .pos-quantity-input,
        .pos-cart-table .pos-tax-rate-input {
            padding: 0.2rem 0.3rem;
        }

        .pos-cart-table .form-control-sm {
            height: 26px;
            font-size: 11px;
        }

        .pos-discount-control {
            align-items: center;
            display: flex;
            gap: .20rem;
        }

        .pos-discount-apply {
            font-size: .62rem;
            padding: .2rem .35rem;
        }

        /* cart dims slightly while a product is being added */
        .pos-busy .cart-items-wrapper {
            opacity: .55;
            pointer-events: none;
            transition: opacity .12s;
        }

        /* ---------- Page shell ---------- */
        .pos-shell {
            align-items: flex-start;
            /* background: #f8fafc; */
            margin: -1.5rem;
            min-height: calc(100vh - 80px);
            padding: 1.5rem;
        }

        .pos-cart .card {
            border: 1px solid #e5eaf0;
            border-radius: 4px;
            box-shadow: none;
        }

        .pos-cart .card-header {
            background: #fff !important;
            border-bottom: 1px solid #e5eaf0;
            color: #273142;
            padding: .8rem 1rem !important;
        }

        .pos-cart .card-header h5 {
            color: #273142 !important;
            font-size: .95rem;
        }

        .pos-cart .card-header .badge {
            background: #2f80ed;
            color: #fff !important;
        }

        .pos-order-fields,
        .pos-product-picker {
            background: #fff;
        }

        .pos-field-label {
            color: #536071;
            display: block;
            font-size: .7rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .pos-order-fields .form-control {
            border-color: #dfe5ec;
            border-radius: 3px;
            font-size: .72rem;
            height: 34px;
        }

        .pos-order-fields .select2-container--default .select2-selection--single {
            border-radius: 3px;
            height: 34px;
        }

        .pos-order-fields .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        .pos-cart .cart-items-wrapper {
            height: 380px !important;
        }

        .pos-cart .btn-lg {
            border-radius: 3px !important;
            font-size: .78rem;
        }

        @media (max-width: 991.98px) {
            .pos-cart {
                margin-bottom: 1rem;
            }
        }

        /* ---------- Product dropdown ---------- */
        .pos-product-picker .select2-container {
            width: 100% !important;
        }

        .pos-product-picker .select2-container--default .select2-selection--single {
            border: 1px solid #dfe5ec;
            border-radius: 3px;
            height: 38px;
        }

        .pos-product-picker .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .pos-product-picker .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .pos-product-picker .select2-container--default.select2-container--focus .select2-selection--single {
            /* border-color: #2f80ed; */
        }

        /* search bar = first row of the dropdown */
        .select2-search--dropdown {
            padding: 8px;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #dfe5ec;
            border-radius: 3px;
            font-size: .8rem;
            padding: 6px 8px;
        }

        /* taller list so more products are visible at once */
        .select2-container--default .select2-results > .select2-results__options {
            max-height: 340px;
        }

        /* dropdown rows (the dropdown itself is rendered in <body>) */
        .pos-opt {
            align-items: center;
            display: flex;
            gap: .6rem;
        }

        .pos-opt-thumb {
            background: #f1f3f6;
            border-radius: 4px;
            flex: 0 0 34px;
            height: 34px;
            object-fit: cover;
            width: 34px;
        }

        .pos-opt-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.2;
        }

        .pos-opt-name {
            font-size: .82rem;
            font-weight: 600;
        }

        .pos-opt-meta {
            font-size: .7rem;
            opacity: .75;
        }

        /* Modern Scrollbar for Cart */
        .cart-items-wrapper::-webkit-scrollbar {
            width: 5px;
        }

        .cart-items-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .cart-items-wrapper::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .cart-items-wrapper::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Select2 Styling Overrides (customer field) */
        .select2-container--default .select2-selection--single {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            height: 45px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 45px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 15px;
            font-size: 0.95rem;
            color: #374151;
        }
    </style>
@endsection