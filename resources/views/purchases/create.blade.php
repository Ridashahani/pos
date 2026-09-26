@extends('dashboard.body.main')

@section('container')
    <style>
        .purchase-create-page {
            background: #f8fafc;
            margin: -1.5rem;
            padding: 1.5rem;
        }

        .purchase-create-page .page-kicker {
            color: #2f80ed;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .purchase-create-page .page-title {
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .purchase-create-page .page-subtitle {
            color: #8490a0;
            font-size: 0.78rem;
        }

        .purchase-create-page .purchase-card {
            border: 1px solid #e5eaf0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(37, 52, 72, 0.04);
        }

        .purchase-create-page .purchase-card .card-body {
            padding: 1rem 1.1rem;
        }

        .purchase-create-page .purchase-table th {
            border-top: 0;
            border-bottom: 1px solid #364152;
            color: #687386;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.35rem 0.45rem;
        }

        .purchase-create-page .purchase-table td {
            border-top: 0;
            color: #273142;
            font-size: 0.78rem;
            padding: 0.45rem;
            vertical-align: middle;
        }

        .purchase-create-page .purchase-table .quantity-input {
            min-width: 50px;
            text-align: center;
            padding-left: 4px;
            padding-right: 4px;
        }

        .purchase-create-page .form-control {
            border-color: #dfe5ec;
            border-radius: 7px;
            color: #364152;
            font-size: 0.78rem;
            height: 34px;
        }

        .purchase-create-page .field-label {
            color: #536071;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .purchase-create-page .side-card-title {
            color: #364152;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
        }

        .purchase-create-page .remove-product {
            color: #ff6b6b;
            font-size: 1.1rem;
        }

        .purchase-create-page .summary-line {
            color: #8490a0;
            font-size: 0.75rem;
        }

        .purchase-create-page .summary-total {
            border-top: 1px solid #9aa3ae;
            color: #364152;
            font-size: 0.78rem;
            font-weight: 700;
            padding-top: 0.55rem;
        }

        .purchase-create-page .due-amount {
            color: #ff5b5b;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .supplier-modal-backdrop {
            align-items: center;
            background: rgba(15, 23, 42, 0.45);
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
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.2);
            max-width: 460px;
            width: 100%;
        }

        .supplier-modal-header {
            border-bottom: 1px solid #e5eaf0;
            color: #364152;
            font-size: 0.95rem;
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

        @media (max-width: 767.98px) {
            .purchase-create-page {
                margin: -1rem;
                padding: 1rem;
            }
        }

        .purchase-create-page .purchase-table {
            table-layout: fixed;
            width: 100%;
        }

        .purchase-create-page .purchase-table td.truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .purchase-create-page .purchase-table .ref-price-input {
            background: #f3f5f8;
            color: #687386;
        }

        .purchase-create-page .product-picker {
            background: #f5f8fb;
            border: 1px solid #e5eaf0;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            padding: 0.9rem;
        }

        .purchase-create-page .empty-row td {
            color: #8490a0;
            font-size: 0.78rem;
            padding: 1.2rem 0.45rem;
            text-align: center;
        }
    </style>

    <div class="container-fluid">
        <div class="purchase-create-page">
            <div class="row">
                <form id="purchase-form" method="POST" action="{{ route('purchases.store') }}" class="w-100">
                    @csrf
                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <div class="page-kicker">New Record</div>
                            <h4 class="page-title">Add Purchase</h4>
                            <p class="page-subtitle mb-0">Create a new purchase from supplier.</p>
                        </div>
                        <a href="{{ route('purchases.index') }}" class="btn btn-light border d-flex align-items-center">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                        </a>
                    </div>
                </div>

                <div class="col-xl-8 mb-4 mb-xl-0">
                    <div class="card purchase-card h-100">
                        <div class="card-body">
                                <div class="product-picker">
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-2 mb-md-0">
                                            <label class="field-label" for="picker-product">Product</label>
                                            <select id="picker-product" class="form-control"></select>
                                        </div>
                                        <div class="col-md-6 form-group mb-0">
                                            <label class="field-label" for="picker-variant">Combination / Variant</label>
                                            <select id="picker-variant" class="form-control" disabled>
                                                <option value="">Select product first</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive rounded">
                                    <table class="table purchase-table mb-0">
                                        <colgroup>
                                            <col style="width:5%">
                                            <col style="width:29%">
                                            <col style="width:9%">
                                            <col style="width:16%">
                                            <col style="width:16%">
                                            <col style="width:19%">
                                            <col style="width:6%">
                                        </colgroup>
                                        <thead class="bg-white text-uppercase">
                                            <tr class="ligth ligth-data">
                                                <th>#</th>
                                                <th>Product / Combination</th>
                                                <th>Qty</th>
                                                {{-- <th>Purchase Price</th> --}}
                                                <th>Purchase Unit Price</th>
                                                <th> Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchase-items" class="ligth-body">
                                            <tr class="empty-row">
                                                <td colspan="6">Select a product & combination above to add it here.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card purchase-card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="side-card-title mb-0">Purchase Details</div>
                                <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm p-1"
                                    aria-label="Add supplier">+</a>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="supplier">Supplier <span
                                        class="text-danger">*</span></label>
                                <select id="supplier" name="supplier_id" class="form-control" required>
                                    <option value="">Select a supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="branch">Branch <span class="text-danger">*</span></label>
                                <select id="branch" name="branch_id" class="form-control" required>
                                    <option value="">Select a branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="field-label" for="purchase-date">Date <span
                                        class="text-danger">*</span></label>
                                <input id="purchase-date" name="purchase_date" type="date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card purchase-card">
                        <div class="card-body">
                            <div class="side-card-title">Payment Summary</div>
                            <div class="d-flex justify-content-between summary-line mb-3">
                                <span>Total amount</span><span id="total-amount" class="text-dark">PKR 0.00</span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="payment-status">Payment Status <span
                                        class="text-danger">*</span></label>
                                <select id="payment-status" name="payment_status" class="form-control" required>
                                    <option>Paid</option>
                                    <option>Partial</option>
                                    <option>Due</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="amount-paid">Amount Paid</label>
                                <input id="amount-paid" name="amount_paid" type="number" class="form-control" value="0" min="0"
                                    step="0.01">
                            </div>
                            <div class="d-flex justify-content-between summary-total">
                                <span>Due Amount</span><span id="due-amount" class="due-amount">PKR 0.00</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-3"
                        onclick="document.getElementById('purchase-form').requestSubmit()">Save Purchase</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div id="supplier-modal-backdrop" class="supplier-modal-backdrop" aria-hidden="true">
        <div class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="supplier-modal-title">
            <div class="supplier-modal-header d-flex align-items-center justify-content-between">
                <span id="supplier-modal-title">Add Supplier</span>
                <button type="button" id="close-supplier-modal" class="supplier-modal-close"
                    aria-label="Close">&times;</button>
            </div>
            <div class="supplier-modal-body">
                <div class="form-group">
                    <label class="field-label" for="new-supplier-name">Name</label>
                    <input id="new-supplier-name" class="form-control" type="text" placeholder="Supplier name">
                </div>
                <div class="form-group">
                    <label class="field-label" for="new-supplier-phone">Phone</label>
                    <input id="new-supplier-phone" class="form-control" type="tel" placeholder="03XX-XXXXXXX">
                </div>
                <div class="form-group mb-3">
                    <label class="field-label" for="new-supplier-address">Address</label>
                    <textarea id="new-supplier-address" class="form-control" rows="3" placeholder="Supplier address"></textarea>
                </div>
                <button type="button" id="save-supplier" class="btn btn-primary btn-block">Add Supplier</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.getElementById('purchase-items');
            const totalAmount = document.getElementById('total-amount');
            const amountPaid = document.getElementById('amount-paid');
            const dueAmount = document.getElementById('due-amount');
            const supplierSelect = document.getElementById('supplier');
            const supplierModal = document.getElementById('supplier-modal-backdrop');
            const openSupplierModal = document.getElementById('open-supplier-modal');
            const closeSupplierModal = document.getElementById('close-supplier-modal');
            const saveSupplier = document.getElementById('save-supplier');
            const pickerProduct = document.getElementById('picker-product');
            const pickerVariant = document.getElementById('picker-variant');
            let rowCounter = 0;

            const PRODUCTS = @json($products);

            function populateProductPicker() {
                pickerProduct.innerHTML = '<option value="">Select Product</option>' +
                    PRODUCTS.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
            }

            function resetVariantPicker() {
                pickerVariant.innerHTML = '<option value="">Select product first</option>';
                pickerVariant.disabled = true;
            }

            pickerProduct.addEventListener('change', function() {
                const product = PRODUCTS.find(p => p.id === Number(this.value));
                if (!product) {
                    resetVariantPicker();
                    return;
                }
                pickerVariant.disabled = false;
                pickerVariant.innerHTML = '<option value="">Select Combination</option>' +
                    product.variants.map(v => `<option value="${v.id}">${v.name}</option>`).join('');
            });

            pickerVariant.addEventListener('change', function() {
                const productId = Number(pickerProduct.value);
                const variantId = String(this.value);
                const product = PRODUCTS.find(p => p.id === productId);
                const variant = product && product.variants.find(v => String(v.id) === variantId);
                if (!product || !variant) return;

                addOrIncrementRow(product, variant);

                pickerProduct.selectedIndex = 0;
                resetVariantPicker();
            });

            function addOrIncrementRow(product, variant) {
                const existing = items.querySelector(`.purchase-item[data-variant-id="${variant.id}"]`);
                if (existing) {
                    const qtyInput = existing.querySelector('.quantity-input');
                    qtyInput.value = Number(qtyInput.value) + 1;
                    existing.querySelector('.quantity-hidden').value = qtyInput.value;
                } else {
                    const emptyRow = items.querySelector('.empty-row');
                    if (emptyRow) emptyRow.remove();

                    const label = `${product.name} — ${variant.name}`;
                    const rowKey = rowCounter++;
                    const row = document.createElement('tr');
                    row.className = 'purchase-item';
                    row.dataset.variantId = variant.id;
                    row.innerHTML =
                        `
                        <td></td>
                        <td class="truncate" title="${label}">${label}</td>
                        <td><input type="number" class="form-control quantity-input" value="1" min="1">
                            <input type="hidden" class="quantity-hidden" name="items[${rowKey}][quantity]" value="1"></td>
                        <td><input type="number" class="form-control cost-input" name="items[${rowKey}][unit_cost]" value="${Number(variant.purchasePrice).toFixed(2)}" min="0" step="0.01">
                            <input type="hidden" name="items[${rowKey}][product_id]" value="${product.id}">
                            <input type="hidden" name="items[${rowKey}][variant]" value="${variant.name}"></td>
                        <td class="amount-cell">PKR 0.00</td>
                        <td><button type="button" class="btn btn-link p-0 remove-product" aria-label="Remove product">&times;</button></td>`;
                    items.appendChild(row);
                }
                updateRowNumbers();
                updateTotals();
            }

            function updateTotals() {
                let total = 0;
                items.querySelectorAll('.purchase-item').forEach(function(row) {
                    const quantity = Math.max(0, Number(row.querySelector('.quantity-input').value) || 0);
                    const unitPrice = Math.max(0, Number(row.querySelector('.cost-input').value) || 0);
                    const amount = quantity * unitPrice;
                    row.querySelector('.amount-cell').textContent = `PKR ${amount.toFixed(2)}`;
                    total += amount;
                });
                const paid = Math.max(0, Number(amountPaid.value) || 0);
                totalAmount.textContent = `PKR ${total.toFixed(2)}`;
                dueAmount.textContent = `PKR ${Math.max(0, total - paid).toFixed(2)}`;
            }

            function updateRowNumbers() {
                const rows = items.querySelectorAll('.purchase-item');
                rows.forEach(function(row, index) {
                    row.querySelector('td').textContent = index + 1;
                });
                if (rows.length === 0) {
                    items.innerHTML =
                        '<tr class="empty-row"><td colspan="7">Select a product & combination above to add it here.</td></tr>';
                }
            }

            populateProductPicker();
            resetVariantPicker();
            amountPaid.addEventListener('input', updateTotals);
            items.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity-input') || e.target.classList.contains(
                        'cost-input')) {
                    if (e.target.classList.contains('quantity-input')) {
                        e.target.closest('.purchase-item').querySelector('.quantity-hidden').value = e.target.value;
                    }
                    updateTotals();
                }
            });
            items.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-product');
                if (!removeButton) return;
                removeButton.closest('.purchase-item').remove();
                updateRowNumbers();
                updateTotals();
            });
            updateTotals();

            function closeModal() {
                supplierModal.classList.remove('is-open');
                supplierModal.setAttribute('aria-hidden', 'true');
            }
            openSupplierModal.addEventListener('click', function() {
                supplierModal.classList.add('is-open');
                supplierModal.setAttribute('aria-hidden', 'false');
            });
            closeSupplierModal.addEventListener('click', closeModal);
            supplierModal.addEventListener('click', function(event) {
                if (event.target === supplierModal) closeModal();
            });
            saveSupplier.addEventListener('click', function() {
                const name = document.getElementById('new-supplier-name').value.trim();
                if (name) {
                    const option = new Option(name, name, true, true);
                    supplierSelect.add(option);
                    closeModal();
                }
            });
        });
    </script>
@endsection
