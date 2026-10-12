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
                <h4 class="card-title mb-0">Stock-In Record</h4>
                <span class="text-muted small">{{ $purchaseItem->purchase?->purchase_no ?: 'Manual stock' }}</span>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <img src="{{ \App\Models\Product::imageUrl($purchaseItem->product?->image) }}"
                            alt="{{ $purchaseItem->product->name }}" class="img-fluid rounded"
                            style="max-height: 180px; object-fit: contain;">
                    </div>
                    <div class="col-md-9">
                        <h5 class="mb-1">{{ $purchaseItem->product->name }}</h5>
                        <p class="text-muted mb-2">Product code: {{ $purchaseItem->product->code }}</p>
                        @if ($barcode)
                            <div class="mb-1">{!! $barcode !!}</div>
                            <small class="text-muted">{{ $purchaseItem->product->code }}</small>
                        @else
                            <span class="text-muted">Barcode unavailable</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <h5 class="mb-3">Product Information</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr><th scope="row" class="w-50">Product Name</th><td>{{ $purchaseItem->product->name }}</td></tr>
                                    <tr><th scope="row">Product Code</th><td>{{ $purchaseItem->product->code }}</td></tr>
                                    <tr><th scope="row">Brand</th><td>{{ $purchaseItem->product->brand?->name ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Model</th><td>{{ $purchaseItem->product->model ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">IMEI / Serial</th><td>{{ $purchaseItem->product->imei ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Category</th><td>{{ $purchaseItem->product->category?->name ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Subcategory</th><td>{{ $purchaseItem->product->subcategory?->name ?: 'Not provided' }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <h5 class="mb-3">Stock-In Information</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr><th scope="row" class="w-50">Batch Reference</th><td>{{ $purchaseItem->batch_no ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Purchase Number</th><td>{{ $purchaseItem->purchase?->purchase_no ?: 'Manual stock' }}</td></tr>
                                    <tr><th scope="row">Purchase Date</th><td>{{ $purchaseItem->purchase?->purchase_date?->format('d M Y') ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Supplier</th><td>{{ $purchaseItem->purchase?->supplier?->name ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Branch</th><td>{{ $purchaseItem->branch?->name ?: 'Not provided' }}</td></tr>
                                    <tr><th scope="row">Variation</th><td>{{ $variationLabel }}</td></tr>
                                    <tr><th scope="row">Purchased Quantity</th><td>{{ number_format($purchaseItem->quantity) }}</td></tr>
                                    <tr>
                                        <th scope="row">Available Quantity</th>
                                        <td>
                                            <span class="badge {{ $purchaseItem->remaining_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ number_format($purchaseItem->remaining_quantity) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr><th scope="row">Unit Cost</th><td>{{ $purchaseItem->product->currency ?: 'PKR' }} {{ number_format($purchaseItem->cost_price, 2) }}</td></tr>
                                    <tr><th scope="row">Stock-In Date</th><td>{{ $purchaseItem->created_at?->format('d M Y') ?: 'Not provided' }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
