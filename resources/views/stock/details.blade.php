@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-2">All Stock-In Product Details</h4>
                <p class="mb-0 text-muted">Complete details for all {{ $products->count() }} Stock-In products.</p>
            </div>
            <a href="{{ route('stock.in') }}" class="btn btn-secondary d-flex align-items-center mt-3 mt-md-0">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Back to Stock-In
            </a>
        </div>

        <div class="card card-block card-stretch">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Product Details</h4>
                <span class="text-muted small">{{ $products->count() }} products</span>
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
                                <th>Expire Date</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold">{{ $product->name }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->brand ?: 'Not provided' }}</td>
                                    <td>{{ $product->model ?: 'Not provided' }}</td>
                                    <td>{{ $product->imei ?: 'Not provided' }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>{{ number_format($product->stock) }}</td>
                                    <td>${{ number_format($product->buying_price, 2) }}</td>
                                    <td>${{ number_format($product->selling_price, 2) }}</td>
                                    <td>{{ $product->buying_date ?: $product->created_at->format('d M Y') }}</td>
                                    <td>{{ $product->expire_date ?: 'Not provided' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
