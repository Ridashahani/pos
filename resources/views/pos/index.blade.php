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
            <!-- LEFT COLUMN: Product Catalog -->
            <div class="col-md-12 col-lg-8 pos-catalog">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-body">
                                <div class="pos-category-tabs mb-3">
                                    <button type="button" class="pos-tab active">All Product</button>
                                    <button type="button" class="pos-tab">Category</button>
                                    <button type="button" class="pos-tab">Brand</button>
                                    <button type="button" class="pos-tab">Featured</button>
                                </div>
                                <!-- Filter & Search Form -->
                                <form action="{{ route('pos.index') }}" method="get" class="mb-0" id="pos-search-form">
                                    <div class="input-group pos-search-box">
                                        <input type="text" class="form-control" name="search" id="pos_search"
                                            placeholder="Search products..." value="{{ request('search') }}"
                                            autocomplete="off" aria-label="Search products">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary pos-search-btn" title="Search">
                                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                            </button>
                                            <button type="button" id="pos-search-clear"
                                                class="btn btn-danger pos-search-btn {{ request('search') || request('category_id') ? '' : 'd-none' }}"
                                                title="Clear search" aria-label="Clear search">
                                                <x-heroicon-o-x-mark class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                    <span id="pos-search-status" class="sr-only" role="status" aria-live="polite"></span>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div id="pos-product-results" class="col-lg-12" aria-busy="false">
                        @include('pos.product-grid', ['products' => $products])
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Cart System -->
            <div class="col-md-12 col-lg-4 pos-cart">
                <div class="card border-0 shadow-lg sticky-top" style="top: 20px; z-index: 100;">
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
                                <div class="col-md-6 form-group mb-2">
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
                                <div class="col-md-6 form-group mb-2">
                                    <label class="pos-field-label">Biller</label>
                                    <select class="form-control">
                                        <option>Admin</option>
                                        <option>Manager</option>
                                        <option>Cashier</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="pos-field-label">Branches</label>
                                    <select class="form-control">
                                        <option>Main Branch</option>
                                        <option>Mobile Store</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="pos-field-label">Reference No</label>
                                    <input class="form-control" type="text" placeholder="Reference No">
                                </div>
                            </div>
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
        // Initialize Select2 on Load
        window.addEventListener('load', function() {
            $('.select2').select2({
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
        });

        // Helper: Get Selected Customer ID
        function getCustomerId() {
            return $('#customer_id').val();
        }

        /* =====================================================================
         * CART ENGINE (same look as before, only the behaviour is fixed)
         * - spinner arrows / typing update the numbers instantly (local maths)
         * - saves are debounced and sent ONE AT A TIME (no lost clicks)
         * - the cart HTML is only re-rendered when nothing is being edited
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
                    t.price += c.unit;
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
            let inflight = 0;
            let lastData = null;
            const timers = new Map();

            function enqueue(task) {
                inflight++;
                chain = chain.then(task)
                    .catch(err => console.error(err))
                    .finally(() => { if (--inflight === 0) settle(); });
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

            // rowId changes whenever options change; keep the live DOM pointing at the current ids.
            function syncRowIds(html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                doc.querySelectorAll('tr[data-item-id]').forEach(n => {
                    const live = rowFor(n.dataset.itemId);
                    if (live) live.dataset.rowId = n.dataset.rowId;
                });
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
                    // No re-render: the numbers on screen were already calculated locally and
                    // match the server. Only keep the row ids in sync so later saves hit the right row.
                    syncRowIds(data.cart_html);
                    if (field === 'qty' && num(value) === 0) render(data);   // line was removed, redraw once
                } else if (data?.cart_html) {
                    render(data);                         // e.g. 409: row vanished, show the real cart
                } else {
                    input.value = input.dataset.saved;    // validation/server error: roll back
                    recalcSummary();
                }
            }

            /* ---------- rendering (only when nothing is being edited) ---------- */
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
                lastData = null;
            }

            function settle() {
                const a = document.activeElement;
                const editing = a && container.contains(a) && a.matches('input, select');
                if (inflight === 0 && timers.size === 0 && lastData && !editing) render(lastData);
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

            /* ---------- events (delegated, so they survive re-renders) ---------- */
            container.addEventListener('click', e => {
                const btn = e.target.closest('[data-action="remove"]');
                const tr = btn?.closest('tr[data-item-id]');
                if (!tr) return;

                const id = tr.dataset.itemId;
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
                if (isField(e.target)) {
                    const input = e.target;
                    const tr = input.closest('tr');
                    if (tr && num(input.value) !== num(input.dataset.saved)) {
                        schedule(tr, fieldOf(input), 150);
                    }
                }
                setTimeout(settle, 0);
            });

            /* ---------- add to cart ---------- */
            function postAdd(formData) {
                flushTimers();
                const cid = getCustomerId();
                if (cid) formData.append('customer_id', cid);
                return enqueue(async () => {
                    const res = await fetch("{{ route('pos.addCart') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const data = await res.json();
                    if (data.success) render(data); else alert(data.message || 'Failed to add item');
                });
            }

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

                    alert('Sale Successful!');

                } else {
                    alert('Sale Failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error submitting sale:', error);
                alert('An error occurred while processing the sale.');
            }
        }

        // AJAX product search
        (function() {
            const posSearchField = document.getElementById('pos_search');
            const searchForm = posSearchField ? posSearchField.closest('form') : null;
            const resultsContainer = document.getElementById('pos-product-results');
            const clearButton = document.getElementById('pos-search-clear');
            const searchStatus = document.getElementById('pos-search-status');

            if (posSearchField && searchForm && resultsContainer) {
                let debounceTimeout;
                let activeRequest;
                let scannerTimeout;

                function syncClearButton() {
                    const hasFilters = posSearchField.value.trim() !== '' || new URLSearchParams(window.location.search)
                        .has('category_id');
                    clearButton.classList.toggle('d-none', !hasFilters);
                }

                function getSearchUrl() {
                    const url = new URL(window.location.href);
                    const search = posSearchField.value.trim();

                    if (search) {
                        url.searchParams.set('search', search);
                    } else {
                        url.searchParams.delete('search');
                    }

                    url.searchParams.delete('page');
                    return url;
                }

                async function loadProducts(url, historyMode = 'replace') {
                    if (activeRequest) {
                        activeRequest.abort();
                    }

                    const requestController = new AbortController();
                    activeRequest = requestController;
                    resultsContainer.setAttribute('aria-busy', 'true');
                    searchForm.setAttribute('aria-busy', 'true');
                    searchStatus.textContent = 'Searching products';

                    try {
                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json'
                            },
                            signal: requestController.signal
                        });

                        if (!response.ok) {
                            throw new Error('Product search failed with status ' + response.status);
                        }

                        const data = await response.json();
                        resultsContainer.innerHTML = data.html;
                        searchStatus.textContent = data.total + (data.total === 1 ? ' product found' :
                            ' products found');

                        if (window.location.href !== url.href) {
                            window.history[historyMode + 'State']({}, '', url);
                        }

                        syncClearButton();
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            console.error('Unable to search products:', error);
                            searchStatus.textContent = 'Unable to load products. Please try again.';
                        }
                    } finally {
                        if (activeRequest === requestController) {
                            resultsContainer.setAttribute('aria-busy', 'false');
                            searchForm.setAttribute('aria-busy', 'false');
                            activeRequest = null;
                        }
                    }
                }

                searchForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    window.clearTimeout(debounceTimeout);
                    loadProducts(getSearchUrl(), 'push');
                });

                posSearchField.addEventListener('input', function() {
                    syncClearButton();
                    window.clearTimeout(debounceTimeout);
                    debounceTimeout = window.setTimeout(function() {
                        loadProducts(getSearchUrl());
                    }, 250);
                });

                clearButton.addEventListener('click', function() {
                    posSearchField.value = '';
                    const url = new URL(searchForm.action);
                    loadProducts(url, 'push');
                });

                resultsContainer.addEventListener('click', function(event) {
                    const link = event.target.closest('.pagination a');
                    if (!link) return;

                    const url = new URL(link.href);
                    if (url.origin !== window.location.origin) return;

                    event.preventDefault();
                    posSearchField.value = url.searchParams.get('search') || '';
                    loadProducts(url, 'push');
                });

                window.addEventListener('popstate', function() {
                    const url = new URL(window.location.href);
                    posSearchField.value = url.searchParams.get('search') || '';
                    loadProducts(url, 'replace');
                });

                // Barcode scanners commonly submit with Enter or paste.
                function isMobileDevice() {
                    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                        (window.innerWidth <= 768);
                }

                posSearchField.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        if (scannerTimeout) {
                            clearTimeout(scannerTimeout);
                        }

                        scannerTimeout = setTimeout(function() {
                            searchForm.requestSubmit();
                        }, 100);
                    }
                });

                posSearchField.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        searchForm.requestSubmit();
                    }, 50);
                });

                if (isMobileDevice()) {
                    setTimeout(function() {
                        posSearchField.focus();
                    }, 500);
                }
            }
        })();
    </script>

    <!-- Page Specific Styles -->
    <style>
        /* ---------- Cart table styles (moved verbatim from the partial so they are not re-injected on every render) ---------- */
        .pos-cart-table {
            min-width: 0;
            width: 100%;
            /* table-layout: fixed; */
            font-size: 10px;

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
            padding: .55rem .4rem;
            white-space: nowrap;
        }

        .pos-cart-table td {
            border-top: 1px solid #edf0f4;
            color: #536071;
            padding: .45rem .4rem;
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
            max-width: 60px;
            padding: 0.25rem 0.3rem;
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

        /* ---------- Product list ---------- */
        .product-grid .pos-product-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #fff;
            border: 1px solid #e5eaf0;
            border-radius: 6px;
            padding: .5rem .65rem;
            transition: box-shadow .2s;
        }

        .product-grid .pos-product-row:hover {
            box-shadow: 0 4px 12px rgba(37, 52, 72, .08);
        }

        /* Image + badge */
        .pos-product-thumb {
            position: relative;
            flex: 0 0 70px;
            width: 70px;
            height: 56px;
        }

        .pos-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
            display: block;
        }

        .pos-stock-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 22px;
            height: 22px;
            padding: 0 5px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        }

        .pos-stock-badge.in {
            background: #10b981;
        }

        .pos-stock-badge.low {
            background: #ef4444;
        }

        /* Name + price */
        .pos-product-info {
            flex: 1 1 auto;
            min-width: 0;
            /* taake naam truncate ho */
        }

        .pos-product-name {
            font-size: .85rem;
            font-weight: 700;
            color: #273142;
            margin: 0 0 .15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-product-price {
            font-size: .9rem;
            font-weight: 800;
        }

        /* Add button */
        .product-grid .add-to-cart-form {
            margin: 0;
            flex-shrink: 0;
        }

        .product-grid .pos-add-btn {
            width: 40px;
            height: 34px;
            padding: 0;
            border-radius: 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff !important;
        }

        .product-grid .pos-add-btn svg {
            width: 18px;
            height: 18px;
            display: block;
            stroke: #fff;
        }

        .product-grid .pos-add-btn:active {
            transform: scale(.94);
        }

        .pos-shell {
            align-items: flex-start;
            background: #f8fafc;
            margin: -1.5rem;
            min-height: calc(100vh - 80px);
            padding: 1.5rem;
        }

        .pos-catalog {
            order: 2;
        }

        .pos-cart {
            order: 1;
        }

        .pos-catalog>.row>.col-lg-12:first-child .card,
        .pos-cart .card {
            border: 1px solid #e5eaf0;
            border-radius: 4px;
            box-shadow: none;
        }

        .pos-catalog>.row>.col-lg-12:first-child .card-body {
            padding: .8rem;
        }

        .pos-category-tabs {
            display: flex;
            flex-wrap: nowrap;
            /* never wrap: always one row */
            gap: .35rem;
        }

        .pos-tab {
            flex: 1 1 0;
            /* four equal-width tabs */
            min-width: 0;
            /* lets them shrink below their text width */
            background: #2f80ed;
            border: 0;
            border-radius: 3px;
            color: #fff;
            font-weight: 700;
            font-size: clamp(.6rem, 1.6vw, .72rem);
            /* text scales down when tight */
            padding: .5rem .25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (min-width: 768px) {
            .pos-tab {
                padding: .5rem .6rem;
            }
        }

        .pos-tab:nth-child(2) {
            background: #2fc48e;
        }

        .pos-tab:nth-child(3) {
            background: #f89a42;
        }

        .pos-tab:nth-child(4) {
            background: #a40c72;
        }

        .pos-search-box {
            flex-wrap: nowrap;
        }

        .pos-search-box .form-control {
            height: 38px;
            font-size: .85rem;
            padding: .4rem .75rem;
            border-radius: 4px 0 0 4px;
        }

        .pos-search-box .pos-search-btn {
            height: 38px;
            width: 42px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
            color: #fff;
        }

        .pos-search-box .input-group-append .pos-search-btn:last-child {
            border-radius: 0 4px 4px 0;
        }

        .pos-search-box .pos-search-btn svg {
            width: 18px;
            height: 18px;
        }

        .pos-cart .card {
            position: sticky;
            top: 20px;
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

        .pos-order-fields {
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

        .product-grid .product-card {
            border: 1px solid #e5eaf0;
            border-radius: 4px;
            box-shadow: none;
        }

        .product-grid .product-card:hover {
            transform: none;
            box-shadow: 0 4px 12px rgba(37, 52, 72, .08);
        }

        .product-grid .product-image {
            height: 105px;
            object-fit: contain;
            padding: .45rem;
        }

        .product-grid .product-card .p-3 {
            padding: .65rem !important;
        }

        .product-grid .product-card h6 {
            font-size: .72rem !important;
            line-height: 1.25;
        }

        .product-grid .product-card h5 {
            font-size: .75rem !important;
        }

        .product-grid .product-card .btn {
            font-size: .68rem;
            padding: .25rem .5rem;
        }

        .pos-cart .cart-items-wrapper {
            height: 285px !important;
        }

        .pos-cart .btn-lg {
            border-radius: 3px !important;
            font-size: .78rem;
        }

        @media (min-width: 992px) {
            .pos-catalog {
                flex: 0 0 36%;
                max-width: 36%;
            }

            .pos-cart {
                flex: 0 0 64%;
                max-width: 64%;
            }

            .pos-catalog .col-lg-3,
            .pos-catalog .col-md-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 991.98px) {

            .pos-catalog,
            .pos-cart {
                max-width: 100%;
            }

            .pos-cart {
                margin-bottom: 1rem;
            }
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

        /* Product Card Hover Effects */
        .product-card {
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #e5e7eb;
        }

        .product-image {
            height: 180px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .image-container {
            overflow: hidden;
            position: relative;
        }

        /* Circular Buttons */
        .btn-circle {
            width: 28px;
            height: 28px;
            padding: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-circle:hover {
            background-color: #f3f4f6;
        }

        /* Select2 Styling Overrides */
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