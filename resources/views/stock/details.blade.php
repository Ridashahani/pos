@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-2">Stock-In Product Details</h4>
                <p class="mb-0 text-muted">Complete details for {{ $purchaseItem->product->name }}.</p>
            </div>
            <a href="{{ route('stock.in') }}" class="btn btn-secondary d-flex align-items-center mt-3 mt-md-0">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Back to Stock-In
            </a>
        </div>

        <div class="card card-block card-stretch">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Purchase Details</h4>
                <span class="text-muted small">{{ $purchaseItem->purchase?->purchase_no ?: 'Manual stock' }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Purchase</th>
                                <th>Purchase Price</th>
                                <th class="text-right text-nowrap">Purchased Qty</th>
                                <th class="text-right text-nowrap">Available Qty</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            <tr>
                                <td class="font-weight-bold">{{ $purchaseItem->product->name }}</td>
                                <td>{{ $purchaseItem->branch->name }}</td>
                                <td>{{ $purchaseItem->purchase?->purchase_no ?: 'Manual' }}</td>
                                <td>{{ $purchaseItem->product->currency ?: 'PKR' }} {{ number_format($purchaseItem->cost_price, 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($purchaseItem->quantity) }}</td>
                                <td class="text-right">
                                    <span class="badge {{ $purchaseItem->remaining_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($purchaseItem->remaining_quantity) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
