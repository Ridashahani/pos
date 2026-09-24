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
                <span class="text-muted small">{{ $purchaseItem->purchase->purchase_number }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>#</th>
                                <th>Product</th>
                                <th>Code</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>IMEI / Serial</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Buying Price</th>
                                <th>Selling Price</th>
                                <th>Buying Date</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            <tr>
                                <td>1</td>
                                <td class="font-weight-bold">{{ $purchaseItem->product->name }}</td>
                                <td>{{ $purchaseItem->product->code }}</td>
                                <td>{{ $purchaseItem->product->brand ?: 'Not provided' }}</td>
                                <td>{{ $purchaseItem->product->model ?: 'Not provided' }}</td>
                                <td>{{ $purchaseItem->imei ?: 'Not provided' }}</td>
                                <td>{{ $purchaseItem->product->category?->name ?: 'Uncategorized' }}</td>
                                <td>{{ number_format($purchaseItem->remaining_qty) }}</td>
                                <td>{{ $purchaseItem->product->currency ?: 'PKR' }} {{ number_format($purchaseItem->cost_price, 2) }}</td>
                                <td>{{ $purchaseItem->product->currency ?: 'PKR' }} {{ number_format($purchaseItem->sale_price, 2) }}</td>
                                <td>{{ $purchaseItem->purchase->purchase_date->format('d M Y') }}</td>
                                <td>{{ $purchaseItem->purchase->supplier?->name ?: 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
