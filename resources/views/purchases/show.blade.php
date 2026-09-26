@extends('dashboard.body.main')

@section('container')
    <style>
        .purchase-show-page {
            background: #f4f6f9;
            margin: -1.5rem;
            padding: 1.5rem;
        }

        .purchase-show-page .section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e2732;
            /* margin-bottom: 0.10rem; */
        }

        .purchase-show-page .field-label {
            color: #8490a0;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .purchase-show-page .field-value {
            color: #1e2732;
            font-size: 1rem;
            font-weight: 700;
        }

        .purchase-show-page .items-table th,
        .purchase-show-page .items-table td {
            font-size: 0.85rem;
            padding: 0.30rem 0.40rem;
            vertical-align: middle;
        }

        .purchase-show-page .items-table thead th {
            color: #8490a0;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #eef1f5;
        }

        .purchase-show-page .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(30, 39, 50, 0.06), 0 1px 2px rgba(30, 39, 50, 0.04);
            padding: 1.5rem;
        }

        .purchase-show-page .info-bar {
            width: 100%;
        }

        .purchase-show-page .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            flex: 1 1 0;
            padding: 0 1.5rem;
            border-right: 1px solid #eef1f5;
        }

        .purchase-show-page .info-item:last-child {
            border-right: none;
        }

        .purchase-show-page .info-item:first-child {
            padding-left: 0;
        }

        .purchase-show-page .info-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eef4ff;
            color: #3b6fe0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .purchase-show-page .info-icon svg {
            width: 18px;
            height: 18px;
        }

        .purchase-show-page .summary-strip {
            width: 100%;
        }

        .purchase-show-page .summary-item {
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            flex: 1 1 0;
        }

        .purchase-show-page .summary-item:first-child {
            padding-left: 0;
        }

        .purchase-show-page .summary-item + .summary-item {
            border-left: 1px solid #eef1f5;
        }

        .purchase-show-page .summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #eef1f5;
            color: #64748b;
        }

        .purchase-show-page .summary-item.paid .summary-icon {
            background: #e5f9f0;
            color: #22a06b;
        }

        .purchase-show-page .summary-item.due .summary-icon {
            background: #fdecec;
            color: #e5484d;
        }

        .purchase-show-page .summary-item.paid .field-value {
            color: #22a06b;
        }

        .purchase-show-page .summary-item.due .field-value {
            color: #e5484d;
        }

        .purchase-show-page .summary-icon svg {
            width: 20px;
            height: 20px;
        }

        .purchase-show-page .status-badge {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
        }
    </style>

    <div class="container-fluid">
        <div class="purchase-show-page">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">Purchase {{ $purchase->purchase_no }}</h4>
                    <p class="mb-0 text-muted">Purchase details and items.</p>
                </div>
                <a href="{{ route('purchases.index') }}" class="btn btn-light border d-flex align-items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                </a>
            </div>

            <div class="section-title">Supplier Details</div>
            <div class="section-card mb-4">
                <div class="info-bar d-flex flex-wrap">
                    <div class="info-item">
                        <div class="info-icon"><x-heroicon-o-truck /></div>
                        <div>
                            <div class="field-label">Supplier</div>
                            <div class="field-value">{{ $purchase->supplier->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><x-heroicon-o-building-storefront /></div>
                        <div>
                            <div class="field-label">Branch</div>
                            <div class="field-value">{{ $purchase->branch->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><x-heroicon-o-calendar /></div>
                        <div>
                            <div class="field-label">Purchase Date</div>
                            <div class="field-value">{{ $purchase->purchase_date->format('Y-m-d') }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><x-heroicon-o-check-badge /></div>
                        <div>
                            <div class="field-label">Status</div>
                            <span class="status-badge badge-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($purchase->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">Product Details</div>
            <div class="section-card mb-4">
                <div class="table-responsive rounded">
                    <table class="table items-table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Variation</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchase->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '-' }}</td>
                                    <td>
                                        @if (!empty($item->variations))
                                            {{ collect($item->variations)->map(fn($v) => ($v['name'] ?? '') . ': ' . ($v['value'] ?? ''))->implode(', ') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>PKR {{ number_format($item->unit_price, 2) }}</td>
                                    <td>PKR {{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section-title">Payment Details</div>
            <div class="section-card">
                <div class="summary-strip d-flex flex-wrap">
                    <div class="summary-item flex-fill">
                        <div class="summary-icon"><x-heroicon-o-banknotes /></div>
                        <div>
                            <div class="field-label">Total Amount</div>
                            <div class="field-value">PKR {{ number_format($purchase->total_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="summary-item paid flex-fill">
                        <div class="summary-icon"><x-heroicon-o-check-circle /></div>
                        <div>
                            <div class="field-label">Paid Amount</div>
                            <div class="field-value">PKR {{ number_format($purchase->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="summary-item due flex-fill">
                        <div class="summary-icon"><x-heroicon-o-exclamation-circle /></div>
                        <div>
                            <div class="field-label">Due Amount</div>
                            <div class="field-value">PKR {{ number_format($purchase->due_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection