@extends('dashboard.body.main')

@section('container')
    <style>
        .invoice-page {
            background: #f5f7fb;
            margin: -1.5rem;
            min-height: calc(100vh - 80px);
            padding: 2rem 1rem;
        }

        .invoice-card {
            background: #fff;
            border: 1px solid #e5eaf0;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(31, 41, 55, .07);
            overflow: hidden;
        }

        .invoice-toolbar {
            background: #fff;
            border-bottom: 1px solid #e5eaf0;
            padding: 1.25rem 1.5rem;
        }

        .invoice-kicker {
            color: #2f80ed;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .invoice-sheet {
            padding: 2.5rem;
        }

        .invoice-brand {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .invoice-number {
            color: #2f80ed;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .invoice-table thead th {
            background: #f5f8fc;
            border: 0;
            color: #657286;
            font-size: .7rem;
            letter-spacing: .05em;
            padding: .8rem .65rem;
            text-transform: uppercase;
        }

        .invoice-table tbody td {
            border-color: #edf0f4;
            color: #3d4858;
            padding: .9rem .65rem;
            vertical-align: middle;
        }

        .invoice-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.1rem;
        }

        .invoice-summary .total-row {
            border-top: 1px solid #dfe5ec;
            color: #1f2937;
            font-size: 1.05rem;
            font-weight: 800;
            margin-top: .6rem;
            padding-top: .8rem;
        }

        @media (max-width: 767.98px) {
            .invoice-sheet {
                padding: 1.25rem;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="invoice-page">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Invoice Preview Card -->
                    <div class="invoice-card">
                        <div class="card-body p-0">

                            <!-- Toolbar: Actions -->
                            <div
                                class="d-flex justify-content-between align-items-center p-4 bg-light border-bottom rounded-top-lg">
                                <div>
                                    <div class="invoice-kicker">Sales document</div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Invoice Preview</h5>
                                    <p class="mb-0 text-muted small">Sale #{{ $sale->invoice_no }}</p>
                                </div>
                                <div>
                                    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Back to POS
                                    </a>
                                    <!-- Opens dedicated Thermal Receipt Popup -->
                                    <button onclick="openPrintWindow()" class="btn btn-dark btn-sm shadow-sm">
                                        <x-heroicon-o-printer class="w-4 h-4 mr-1 inline" /> Print Receipt
                                    </button>
                                </div>
                            </div>

                            <!-- Visual Invoice Representation -->
                            <div class="invoice-sheet">
                                <!-- Company Header -->
                                <div class="row mb-5">
                                    <div class="col-6">
                                        <h3 class="invoice-brand mb-1">POS SHOP</h3>
                                        <p class="text-muted mb-0">123 Commerce Avenue</p>
                                        <p class="text-muted">Islamabad, Pakistan</p>
                                    </div>
                                    <div class="col-6 text-right">
                                        <h6 class="text-uppercase text-muted font-weight-bold letter-spacing-2 mb-2">Invoice
                                        </h6>
                                        <h4 class="invoice-number mb-0">{{ $sale->invoice_no }}</h4>
                                        <p class="text-muted small mb-0">{{ $sale->created_at->format('d M Y, H:i') }}</p>
                                        <span class="badge badge-success mt-1 px-3 py-1">PAID</span>
                                    </div>
                                </div>

                                <hr class="border-light my-4">

                                <!-- Client & Cashier Info -->
                                <div class="row mb-5">
                                    <div class="col-6">
                                        <p class="text-uppercase text-muted small font-weight-bold mb-2">Billed To</p>
                                        <h6 class="font-weight-bold text-dark mb-1">{{ $sale->customer->name }}</h6>
                                        <p class="text-muted small mb-0">{{ $sale->customer->phone ?? '' }}</p>
                                    </div>
                                    <div class="col-6 text-right">
                                        <p class="text-uppercase text-muted small font-weight-bold mb-2">Cashier</p>
                                        <h6 class="font-weight-bold text-dark mb-0">{{ auth()->user()->name ?? 'Staff' }}
                                        </h6>
                                    </div>
                                </div>

                                <!-- Sale Items Table -->
                                <div class="table-responsive mb-4">
                                    <table class="table invoice-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 font-weight-bold text-muted small text-uppercase pl-4">
                                                    Item</th>
                                                <th
                                                    class="border-0 font-weight-bold text-muted small text-uppercase text-center">
                                                    Qty</th>
                                                <th
                                                    class="border-0 font-weight-bold text-muted small text-uppercase text-right">
                                                    Price</th>
                                                <th
                                                    class="border-0 font-weight-bold text-muted small text-uppercase text-right">
                                                    Discount</th>
                                                <th
                                                    class="border-0 font-weight-bold text-muted small text-uppercase text-right pr-4">
                                                    Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($saleDetails as $item)
                                                <tr>
                                                    <td class="border-bottom-0 pl-4 py-3">
                                                        <p class="font-weight-bold text-dark mb-0">
                                                            {{ $item->product->name }}</p>
                                                    </td>
                                                    <td class="border-bottom-0 text-center py-3">{{ $item->quantity }}</td>
                                                    <td class="border-bottom-0 text-right py-3">
                                                        {{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="border-bottom-0 text-right py-3 text-danger">-
                                                        {{ number_format($item->discount ?? 0, 2) }}</td>
                                                    <td class="border-bottom-0 text-right py-3 pr-4 font-weight-bold">
                                                        {{ number_format($item->total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Calculation Totals -->
                                <div class="row">
                                    <div class="col-md-5 ml-auto">
                                        <div class="invoice-summary">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted">Subtotal</td>
                                                    <td class="text-right font-weight-bold">
                                                        {{ number_format($sale->sub_total, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Discount</td>
                                                    <td class="text-danger text-right font-weight-bold">-
                                                        {{ number_format($saleDetails->sum('discount'), 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Tax (VAT)</td>
                                                    <td class="text-right font-weight-bold">
                                                        {{ number_format($sale->vat, 2) }}</td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td class="text-dark font-weight-bold pt-3 h5">Total</td>
                                                    <td class="text-primary font-weight-bold text-right pt-3 h5">
                                                        {{ number_format($sale->total, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Paid</td>
                                                    <td class="text-success text-right font-weight-bold">
                                                        {{ number_format($sale->pay_amount, 2) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Opens the dedicated Thermal Receipt View in a new small window for printing.
         */
        function openPrintWindow() {
            const url = "{{ route('sale.printReceipt', $sale->id) }}";
            const width = 400;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;

            window.open(url, 'PrintReceipt', `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`);
        }
    </script>
@endsection
