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
            font-size: 0.40rem;
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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
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
            </div>

            <form id="purchase-form" method="POST" action="{{ route('purchases.store') }}">
                @csrf

                <div class="row">
                    <div class="col-xl-8 mb-4 mb-xl-0">
                        <div class="card purchase-card h-100">
                            <div class="card-body">
                                <div class="product-picker">
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-2 mb-md-0">
                                            <label class="field-label" for="picker-product">Product</label>
                                            <select id="picker-product" class="form-control">
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->buying_price }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-2 mb-md-0">
                                            <label class="field-label" for="picker-variation-type">Variation</label>
                                            <select id="picker-variation-type" class="form-control">
                                                <option value="">Select Variation</option>
                                                @foreach ($variations as $variation)
                                                    <option value="{{ $variation->id }}" data-name="{{ $variation->name }}"
                                                        data-types='@json($variation->types)'>{{ $variation->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-2 mb-md-0">
                                            <label class="field-label" for="picker-variation-value">Value</label>
                                            <select id="picker-variation-value" class="form-control" disabled>
                                                <option value="">Select variation first</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group mb-0 d-flex align-items-end">
                                            <button type="button" id="add-variation-tag" class="btn btn-light border w-100">+ Add</button>
                                        </div>
                                    </div>
                                    <div id="selected-variations" class="d-flex flex-wrap mt-2"></div>
                                    <button type="button" id="add-product-row" class="btn btn-primary btn-sm mt-3">Add Product to Purchase</button>
                                </div>

                                <div class="table-responsive rounded">
                                    <table class="table purchase-table mb-0">
                                        <thead class="bg-white text-uppercase">
                                            <tr class="ligth ligth-data">
                                                <th>#</th>
                                                <th>Product / Variation</th>
                                                <th>Qty</th>
                                                <th>Purchase Unit Price</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchase-items" class="ligth-body">
                                            <tr class="empty-row">
                                                <td colspan="6">Select a product & variation above to add it here.</td>
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
                                    <button type="button" id="open-supplier-modal" class="btn btn-primary btn-sm p-1"
                                        aria-label="Add supplier">+</button>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="supplier">Supplier <span class="text-danger">*</span></label>
                                    <select id="supplier" name="supplier_id" class="form-control">
                                        <option value="">Select a supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="branch">Branch <span class="text-danger">*</span></label>
                                    <select id="branch" name="branch_id" class="form-control">
                                        <option value="">Select a branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="field-label" for="purchase-date">Date <span class="text-danger">*</span></label>
                                    <input id="purchase-date" name="purchase_date" type="date" class="form-control" value="{{ date('Y-m-d') }}">
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
                                    <label class="field-label" for="payment-status">Payment Status <span class="text-danger">*</span></label>
                                    <select id="payment-status" name="payment_status" class="form-control">
                                        <option value="paid">Paid</option>
                                        <option value="partial">Partial</option>
                                        <option value="due">Due</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="amount-paid">Amount Paid</label>
                                    <input id="amount-paid" name="paid_amount" type="number" class="form-control" value="0" min="0" step="0.01">
                                </div>
                                <div class="d-flex justify-content-between summary-total">
                                    <span>Due Amount</span><span id="due-amount" class="due-amount">PKR 0.00</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="save-purchase" class="btn btn-primary btn-block mt-3">Save Purchase</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="supplier-modal-backdrop" class="supplier-modal-backdrop" aria-hidden="true">
        <div class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="supplier-modal-title">
            <div class="supplier-modal-header d-flex align-items-center justify-content-between">
                <span id="supplier-modal-title">Add Supplier</span>
                <button type="button" id="close-supplier-modal" class="supplier-modal-close" aria-label="Close">&times;</button>
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
                <p class="text-muted" style="font-size:.75rem;">Naya supplier abhi sirf isi page pe dikhega — DB mein save nahi hoga jab tak backend wire na karein (agla step).</p>
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
            const pickerVariationType = document.getElementById('picker-variation-type');
            const pickerVariationValue = document.getElementById('picker-variation-value');
            const addVariationTag = document.getElementById('add-variation-tag');
            const selectedVariationsBox = document.getElementById('selected-variations');
            const addProductRow = document.getElementById('add-product-row');
            const purchaseForm = document.getElementById('purchase-form');
            const savePurchaseBtn = document.getElementById('save-purchase');

            let selectedVariations = [];
            let rowCounter = 0;
            const itemsData = {};

            pickerVariationType.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const types = selectedOption.value ? JSON.parse(selectedOption.dataset.types || '[]') : [];

                if (!types.length) {
                    pickerVariationValue.innerHTML = '<option value="">Select variation first</option>';
                    pickerVariationValue.disabled = true;
                    return;
                }
                pickerVariationValue.disabled = false;
                pickerVariationValue.innerHTML = '<option value="">Select Value</option>' +
                    types.map(t => `<option value="${t}">${t}</option>`).join('');
            });

            addVariationTag.addEventListener('click', function() {
                const typeOption = pickerVariationType.options[pickerVariationType.selectedIndex];
                const variationId = pickerVariationType.value;
                const value = pickerVariationValue.value;
                if (!variationId || !value) return;

                selectedVariations = selectedVariations.filter(v => v.variation_id !== variationId);
                selectedVariations.push({
                    variation_id: variationId,
                    name: typeOption.dataset.name,
                    value: value
                });
                renderSelectedVariations();

                pickerVariationType.selectedIndex = 0;
                pickerVariationValue.innerHTML = '<option value="">Select variation first</option>';
                pickerVariationValue.disabled = true;
            });

            function renderSelectedVariations() {
                selectedVariationsBox.innerHTML = selectedVariations.map(v =>
                    `<span class="badge badge-light border mr-1 mb-1 p-2">${v.name}: ${v.value}
                        <a href="javascript:void(0)" class="text-danger ml-1 remove-variation-tag" data-id="${v.variation_id}">&times;</a>
                    </span>`
                ).join('');
            }

            selectedVariationsBox.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-variation-tag');
                if (!removeBtn) return;
                selectedVariations = selectedVariations.filter(v => v.variation_id !== removeBtn.dataset.id);
                renderSelectedVariations();
            });

            addProductRow.addEventListener('click', function() {
                const productOption = pickerProduct.options[pickerProduct.selectedIndex];
                const productId = pickerProduct.value;
                if (!productId) {
                    alert('Pehle product select karein.');
                    return;
                }

                rowCounter++;
                const rowId = 'row-' + rowCounter;
                const unitPrice = Number(productOption.dataset.price || 0);
                const variationLabel = selectedVariations.map(v => `${v.name}: ${v.value}`).join(', ') || 'Standard';

                itemsData[rowId] = {
                    product_id: productId,
                    quantity: 1,
                    unit_price: unitPrice,
                    variations: [...selectedVariations],
                };

                const emptyRow = items.querySelector('.empty-row');
                if (emptyRow) emptyRow.remove();

                const row = document.createElement('tr');
                row.className = 'purchase-item';
                row.dataset.rowId = rowId;
                row.innerHTML = `
                    <td></td>
                    <td class="truncate" title="${productOption.text} — ${variationLabel}">${productOption.text} — ${variationLabel}</td>
                    <td><input type="number" class="form-control quantity-input" value="1" min="1"></td>
                    <td><input type="number" class="form-control cost-input" value="${unitPrice.toFixed(2)}" min="0" step="0.01"></td>
                    <td class="amount-cell">PKR 0.00</td>
                    <td><button type="button" class="btn btn-link p-0 remove-product" aria-label="Remove product">&times;</button></td>`;
                items.appendChild(row);

                selectedVariations = [];
                renderSelectedVariations();
                pickerProduct.selectedIndex = 0;

                updateRowNumbers();
                updateTotals();
            });

            function updateTotals() {
                let total = 0;
                items.querySelectorAll('.purchase-item').forEach(function(row) {
                    const rowId = row.dataset.rowId;
                    const quantity = Math.max(0, Number(row.querySelector('.quantity-input').value) || 0);
                    const unitPrice = Math.max(0, Number(row.querySelector('.cost-input').value) || 0);
                    const amount = quantity * unitPrice;
                    row.querySelector('.amount-cell').textContent = `PKR ${amount.toFixed(2)}`;
                    total += amount;

                    if (itemsData[rowId]) {
                        itemsData[rowId].quantity = quantity;
                        itemsData[rowId].unit_price = unitPrice;
                    }
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
                    items.innerHTML = '<tr class="empty-row"><td colspan="6">Select a product & variation above to add it here.</td></tr>';
                }
            }

            amountPaid.addEventListener('input', updateTotals);
            items.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity-input') || e.target.classList.contains('cost-input')) {
                    updateTotals();
                }
            });
            items.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-product');
                if (!removeButton) return;
                const rowId = removeButton.closest('.purchase-item').dataset.rowId;
                delete itemsData[rowId];
                removeButton.closest('.purchase-item').remove();
                updateRowNumbers();
                updateTotals();
            });
            updateTotals();

            savePurchaseBtn.addEventListener('click', function() {
                const rows = Object.values(itemsData);
                if (!rows.length) {
                    alert('Kam az kam ek product add karein.');
                    return;
                }
                if (!supplierSelect.value) {
                    alert('Supplier select karein.');
                    return;
                }
                if (!document.getElementById('branch').value) {
                    alert('Branch select karein.');
                    return;
                }

                rows.forEach((item, index) => {
                    appendHiddenInput(`items[${index}][product_id]`, item.product_id);
                    appendHiddenInput(`items[${index}][quantity]`, item.quantity);
                    appendHiddenInput(`items[${index}][unit_price]`, item.unit_price);
                    item.variations.forEach((v, vIndex) => {
                        appendHiddenInput(`items[${index}][variations][${vIndex}][variation_id]`, v.variation_id);
                        appendHiddenInput(`items[${index}][variations][${vIndex}][name]`, v.name);
                        appendHiddenInput(`items[${index}][variations][${vIndex}][value]`, v.value);
                    });
                });

                purchaseForm.submit();
            });

            function appendHiddenInput(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                purchaseForm.appendChild(input);
            }

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