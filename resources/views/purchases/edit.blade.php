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


        .purchase-create-page .purchase-table td {
            border-top: 0;
            color: #273142;
            font-size: 0.78rem;
            padding: 0.45rem;
            vertical-align: middle;
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

        .purchase-create-page .purchase-table {
            width: 100%;
        }

        .purchase-create-page .purchase-table th {
            white-space: nowrap;
            font-size: 0.78rem;
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

        .purchase-list-table th {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.4rem 0.5rem;
            text-transform: uppercase;
            white-space: nowrap;
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

            <form id="purchase-form" method="POST" action="{{ route('purchases.update', $purchase) }}">
                @csrf
                @method('PUT')

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <div class="page-kicker">Edit Record</div>
                        <h4 class="page-title">Edit Purchase — {{ $purchase->purchase_no }}</h4>
                        <p class="page-subtitle mb-0">Update purchase details and items.</p>
                    </div>
                    <a href="{{ route('purchases.index') }}" class="btn btn-light border d-flex align-items-center">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>

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
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->buying_price }}">{{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-2 mb-md-0">
                                            <label class="field-label" for="picker-variation-type">Variation</label>
                                            <select id="picker-variation-type" class="form-control">
                                                <option value="">Select Variation</option>
                                                @foreach ($variations as $variation)
                                                    <option value="{{ $variation->id }}" data-name="{{ $variation->name }}"
                                                        data-types='@json($variation->types)'>{{ $variation->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="field-label" for="picker-variation-value">Value</label>
                                            <select id="picker-variation-value" class="form-control" disabled>
                                                <option value="">Select variation first</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive rounded">
                                    <table class="table purchase-table mb-0">
                                        <thead class="bg-white text-uppercase">
                                            <tr>
                                                <th>#</th>
                                                <th>Product / Variation</th>
                                                <th>Qty</th>
                                                <th>Purchase Unit Price</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchase-items" class="ligth-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card purchase-card mb-3">
                            <div class="card-body">
                                <div class="side-card-title mb-2">Purchase Details</div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="supplier">Supplier <span
                                            class="text-danger">*</span></label>
                                    <select id="supplier" name="supplier_id" class="form-control" required>
                                        <option value="">Select a supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" @selected($supplier->id === $purchase->supplier_id)>
                                                {{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="branch">Branch <span
                                            class="text-danger">*</span></label>
                                    <select id="branch" name="branch_id" class="form-control" required>
                                        <option value="">Select a branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected($branch->id === $purchase->branch_id)>
                                                {{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="field-label" for="purchase-date">Date <span
                                            class="text-danger">*</span></label>
                                    <input id="purchase-date" name="purchase_date" type="date" class="form-control"
                                        value="{{ $purchase->purchase_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="card purchase-card">
                            <div class="card-body">
                                <div class="side-card-title">Payment Summary</div>
                                <div class="d-flex justify-content-between summary-line mb-3">
                                    <span>Total amount</span><span id="total-amount" class="text-dark">PKR
                                        {{ number_format($purchase->total_amount, 2) }}</span>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="payment-status">Payment Status <span
                                            class="text-danger">*</span></label>
                                    <select id="payment-status" name="payment_status" class="form-control" required>
                                        <option value="paid" @selected($purchase->payment_status === 'paid')>Paid</option>
                                        {{-- <option value="partial" @selected($purchase->payment_status === 'partial')>Partial</option> --}}
                                        <option value="due" @selected($purchase->payment_status === 'due')>Due</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="field-label" for="amount-paid">Amount Paid</label>
                                    <input id="amount-paid" name="paid_amount" type="number" class="form-control"
                                        value="{{ $purchase->paid_amount }}" min="0" step="0.01">
                                </div>
                                <div class="d-flex justify-content-between summary-total">
                                    <span>Due Amount</span><span id="due-amount" class="due-amount">PKR
                                        {{ number_format($purchase->due_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-3">Update Purchase</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $existingItemsData = $purchase->items
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'variations' => $item->variations ?? [],
                ];
            })
            ->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.getElementById('purchase-items');
            const totalAmount = document.getElementById('total-amount');
            const amountPaid = document.getElementById('amount-paid');
            const dueAmount = document.getElementById('due-amount');

            const pickerProduct = document.getElementById('picker-product');
            const pickerVariationType = document.getElementById('picker-variation-type');
            const pickerVariationValue = document.getElementById('picker-variation-value');

            let rowCounter = 0;
            const existingItems = @json($existingItemsData);

            existingItems.forEach(function(item) {
                const variationLabel = (item.variations || [])
                    .map(v => `${v.name}: ${v.value}`).join(', ') || 'Standard';
                addRowFromData(item.product_id, item.product_name, variationLabel, item.quantity, item
                    .unit_price, item.variations);
            });

            pickerVariationType.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                let types = [];
                if (selectedOption.value) {
                    try {
                        types = JSON.parse(selectedOption.dataset.types || '[]');
                    } catch (e) {
                        types = [];
                    }
                }
                types = types
                    .map(t => (typeof t === 'object' && t !== null) ? (t.value ?? t.name ?? '') : t)
                    .filter(t => t !== '' && t !== null && t !== undefined);

                if (!types.length) {
                    pickerVariationValue.innerHTML = '<option value="">Select variation first</option>';
                    pickerVariationValue.disabled = true;
                    return;
                }
                pickerVariationValue.disabled = false;
                pickerVariationValue.innerHTML = '<option value="">Select Value</option>' +
                    types.map(t => `<option value="${t}">${t}</option>`).join('');
            });

            pickerVariationValue.addEventListener('change', function() {
                const productId = pickerProduct.value;
                const productOption = pickerProduct.options[pickerProduct.selectedIndex];
                const variationId = pickerVariationType.value;
                const variationTypeOption = pickerVariationType.options[pickerVariationType.selectedIndex];
                const value = this.value;

                if (!productId) {
                    alert('Pehle product select karein.');
                    this.value = '';
                    return;
                }
                if (!variationId || !value) return;

                const unitPrice = Number(productOption.dataset.price || 0);
                const variations = [{
                    variation_id: variationTypeOption.value,
                    name: variationTypeOption.dataset.name,
                    value: value,
                }];

                addRowFromData(productOption.value, productOption.text,
                    `${variationTypeOption.dataset.name}: ${value}`, 1, unitPrice, variations);

                pickerProduct.selectedIndex = 0;
                pickerVariationType.selectedIndex = 0;
                pickerVariationValue.innerHTML = '<option value="">Select variation first</option>';
                pickerVariationValue.disabled = true;
            });

            function addRowFromData(productId, productName, variationLabel, quantity, unitPrice, variations) {
                const rowId = rowCounter++;
                const label = `${productName} — ${variationLabel}`;

                const row = document.createElement('tr');
                row.className = 'purchase-item';
                row.dataset.rowId = rowId;

                let variationInputs = '';
                (variations || []).forEach((v, vIndex) => {
                    variationInputs += `
                        <input type="hidden" name="items[${rowId}][variations][${vIndex}][variation_id]" value="${v.variation_id}">
                        <input type="hidden" name="items[${rowId}][variations][${vIndex}][name]" value="${v.name}">
                        <input type="hidden" name="items[${rowId}][variations][${vIndex}][value]" value="${v.value}">`;
                });

                row.innerHTML = `
                    <td></td>
                    <td class="truncate" title="${label}">${label}</td>
                    <td><input type="number" class="form-control quantity-input" name="items[${rowId}][quantity]" value="${quantity}" min="1"></td>
                    <td><input type="number" class="form-control cost-input" name="items[${rowId}][unit_price]" value="${Number(unitPrice).toFixed(2)}" min="0" step="0.01"></td>
                    <td class="amount-cell">PKR ${(quantity * unitPrice).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-link p-0 remove-product" aria-label="Remove product">&times;</button>
                        <input type="hidden" name="items[${rowId}][product_id]" value="${productId}">
                        ${variationInputs}
                    </td>`;
                items.appendChild(row);
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
                        '<tr class="empty-row"><td colspan="6">Product, select variation and its value </td></tr>';
                }
            }

            amountPaid.addEventListener('input', updateTotals);
            items.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity-input') || e.target.classList.contains(
                        'cost-input')) {
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

            document.getElementById('purchase-form').addEventListener('submit', function(e) {
                if (!items.querySelector('.purchase-item')) {
                    e.preventDefault();
                    alert('Please add at least one product..');
                }
            });

            updateTotals();
        });
    </script>
@endsection
