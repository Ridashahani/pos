@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0;
            /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        @media (min-width: 576px) {
            .row-selector-container {
                min-width: 180px;
                /* Apply min-width only on sm+ screens */
                padding-top: 0;
                padding-bottom: 0;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert text-white bg-danger" role="alert">
                        <div class="iq-alert-text">{{ session('error') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Product List</h4>
                        <p class="mb-0">A product dashboard lets you easily gather and visualize product data from
                            optimizing <br>
                            the product experience, ensuring product retention. </p>
                    </div>
                    <!-- begin: Action Buttons -->
                    <div class="d-flex align-items-center">
                        {{-- <a href="{{ route('products.importView') }}" class="btn btn-success add-list mr-2 d-flex align-items-center">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 mr-1" /> Import
                    </a> --}}
                        {{-- <a href="{{ route('products.exportData') }}" class="btn btn-warning add-list mr-2 d-flex align-items-center">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-1" /> Export
                    </a> --}}
                        @can('create.products')
                            <a href="{{ route('products.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                                <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Product
                            </a>
                        @endcan
                    </div>
                    <!-- end: Action Buttons -->
                </div>
            </div>

            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search by product name..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request('search'))
                        <a href="{{ route('products.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </a>
                        @endif
                    </form>
                    @if (isset($products) && method_exists($products, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $products->total() }} records</span>
                    @endif
                </div>
            </div>

            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th>Photo</th>
                                <th><x-sort-link name="name" label="Name" /></th>
                                <th><x-sort-link name="category.name" label="Category" /></th>
                                <th><x-sort-link name="selling_price" label="Price" /></th>
                                <th><x-sort-link name="stock" label="Stock" /></th>
                                <th><x-sort-link name="status" label="Status" /></th>


                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $products->currentPage() * 10 - 10 + $loop->iteration }}</td>
                                    <td>
                                        <img class="avatar-60 rounded"
                                            src="{{ \App\Models\Product::imageUrl($product->image) }}"
                                            alt="{{ $product->name }}">
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>{{ number_format($product->price_including_gst, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        @if ($product->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center list-action">
                                            <a class="btn btn-info mr-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="View" href="{{ route('products.show', $product->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            @can('edit.products')
                                                <a class="btn btn-success mr-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Edit" href="{{ route('products.edit', $product->id) }}">
                                                    <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                </a>
                                            @endcan
                                            @can('delete.products')
                                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger border-0"
                                                        onclick="return confirm('Are you sure you want to delete this record?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                        <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <span class="text-muted">No products found.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
