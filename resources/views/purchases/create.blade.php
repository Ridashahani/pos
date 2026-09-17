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

        .purchase-create-page .add-product-row {
            border: 2px dashed #dce3eb;
            border-radius: 8px;
            color: #8490a0;
            cursor: default;
            font-size: 0.78rem;
            padding: 0.48rem;
            text-align: center;
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

            .purchase-create-page .purchase-table {
                min-width: 590px;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="purchase-create-page">
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

                <div class="col-xl-8 mb-4 mb-xl-0">
                    <div class="card purchase-card h-100">
                        <div class="card-body">
                            <form>
                                <div class="table-responsive rounded">
                                    <table class="table purchase-table mb-0">
                                        <thead class="bg-white text-uppercase">
                                            <tr class="ligth ligth-data">
                                                <th>#</th>
                                                <th>Product</th>
                                                <th width="120">Quantity</th>
                                                <th width="150">Cost Price</th>
                                                <th width="130">Amount</th>
                                                <th width="32"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchase-items" class="ligth-body">
                                            <tr class="purchase-item">
                                                <td>1</td>
                                                <td>
                                                    <select class="form-control product-select">
                                                        <option>Select Product</option>
                                                        <option>Apple iPhone 15</option>
                                                        <option>Samsung Galaxy S24</option>
                                                        <option>Anker 20W Fast Charger</option>
                                                        <option>Type-C Fast Charging Cable</option>
                                                        <option>iPhone 15 Silicone Case</option>
                                                        <option>9D Tempered Glass Protector</option>
                                                        <option>Anker 10000mAh Power Bank</option>
                                                        <option>AirPods Pro 2</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control quantity-input" value="1"
                                                        min="1"></td>
                                                <td><input type="number" class="form-control cost-input" value="0"
                                                        min="0" step="0.01"></td>
                                                <td class="amount-cell">PKR 0.00</td>
                                                <td><button type="button" class="btn btn-link p-0 remove-product"
                                                        aria-label="Remove product">&times;</button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="add-product" class="add-product-row btn btn-block mt-3">+ Add
                                    Product</button>
                            </form>
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
                                <label class="field-label" for="supplier">Supplier <span
                                        class="text-danger">*</span></label>
                                <select id="supplier" class="form-control">
                                    <option>Select a supplier</option>
                                    <option>Metro Wholesale</option>
                                    <option>Fresh Foods Ltd.</option>
                                    <option>City Distributors</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="branch">Branch <span class="text-danger">*</span></label>
                                <select id="branch" class="form-control">
                                    <option>Select a branch</option>
                                    <option>Main Branch</option>
                                    <option>North Branch</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="field-label" for="purchase-date">Date <span
                                        class="text-danger">*</span></label>
                                <input id="purchase-date" type="date" class="form-control" value="2026-09-15">
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
                                <select id="payment-status" class="form-control">
                                    <option>Paid</option>
                                    <option>Partial</option>
                                    <option>Due</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="field-label" for="amount-paid">Amount Paid</label>
                                <input id="amount-paid" type="number" class="form-control" value="0" min="0"
                                    step="0.01">
                            </div>
                            <div class="d-flex justify-content-between summary-total">
                                <span>Due Amount</span><span id="due-amount" class="due-amount">PKR 0.00</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-3"
                        onclick="alert('Purchase saved successfully. Static data was not stored.')">Save Purchase</button>
                </div>
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
            const addProduct = document.getElementById('add-product');
            const totalAmount = document.getElementById('total-amount');
            const amountPaid = document.getElementById('amount-paid');
            const dueAmount = document.getElementById('due-amount');
            const supplierSelect = document.getElementById('supplier');
            const supplierModal = document.getElementById('supplier-modal-backdrop');
            const openSupplierModal = document.getElementById('open-supplier-modal');
            const closeSupplierModal = document.getElementById('close-supplier-modal');
            const saveSupplier = document.getElementById('save-supplier');
            const productOptions = `
                <option>Select Product</option>
                <option>Apple iPhone 15</option>
                <option>Samsung Galaxy S24</option>
                <option>Anker 20W Fast Charger</option>
                <option>Type-C Fast Charging Cable</option>
                <option>iPhone 15 Silicone Case</option>
                <option>9D Tempered Glass Protector</option>
                <option>Anker 10000mAh Power Bank</option>
                <option>AirPods Pro 2</option>`;

            function updateTotals() {
                let total = 0;

                items.querySelectorAll('.purchase-item').forEach(function(row) {
                    const quantity = Math.max(0, Number(row.querySelector('.quantity-input').value) || 0);
                    const cost = Math.max(0, Number(row.querySelector('.cost-input').value) || 0);
                    const amount = quantity * cost;

                    row.querySelector('.amount-cell').textContent = `PKR ${amount.toFixed(2)}`;
                    total += amount;
                });

                const paid = Math.max(0, Number(amountPaid.value) || 0);
                totalAmount.textContent = `PKR ${total.toFixed(2)}`;
                dueAmount.textContent = `PKR ${Math.max(0, total - paid).toFixed(2)}`;
            }

            function updateRowNumbers() {
                items.querySelectorAll('.purchase-item').forEach(function(row, index) {
                    row.querySelector('td').textContent = index + 1;
                });
            }

            function addProductRow() {
                const row = document.createElement('tr');
                row.className = 'purchase-item';
                row.innerHTML =
                    `
                    <td></td>
                    <td><select class="form-control product-select">${productOptions}</select></td>
                    <td><input type="number" class="form-control quantity-input" value="1" min="1"></td>
                    <td><input type="number" class="form-control cost-input" value="0" min="0" step="0.01"></td>
                    <td class="amount-cell">PKR 0.00</td>
                    <td><button type="button" class="btn btn-link p-0 remove-product" aria-label="Remove product">&times;</button></td>`;
                items.appendChild(row);
                updateRowNumbers();
            }

            addProduct.addEventListener('click', addProductRow);
            amountPaid.addEventListener('input', updateTotals);
            items.addEventListener('input', updateTotals);
            items.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-product');

                if (!removeButton) {
                    return;
                }

                const rows = items.querySelectorAll('.purchase-item');
                if (rows.length > 1) {
                    removeButton.closest('.purchase-item').remove();
                    updateRowNumbers();
                } else {
                    const row = removeButton.closest('.purchase-item');
                    row.querySelector('.product-select').selectedIndex = 0;
                    row.querySelector('.quantity-input').value = 1;
                    row.querySelector('.cost-input').value = 0;
                }

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
                if (event.target === supplierModal) {
                    closeModal();
                }
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
