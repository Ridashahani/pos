@extends('dashboard.body.main')
@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Barcode</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="form-group col-md-6">
                                <label>Product Code</label>
                                <input type="text" class="form-control bg-white" value="{{ $product->code }}" readonly>
                            </div>
                            {{-- <div class="form-group col-md-6">
                                <label>Product Barcode</label>
                                {!! $barcode !!}
                            </div> --}}
                        </div>
                        <!-- end: Show Data -->
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Product Information</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- begin: Show Data -->
                        <div class="form-group row align-items-center">
                            <div class="col-md-12 d-flex flex-wrap">
                                @forelse ($product->images ?? [] as $img)
                                    <img class="rounded mr-2 mb-2" style="width:100px;height:100px;object-fit:cover;"
                                        src="{{ \App\Models\Product::imageUrl($img) }}" alt="{{ $product->name }}">
                                @empty
                                    <img class="rounded mr-2 mb-2" style="width:100px;height:100px;object-fit:cover;"
                                        src="{{ \App\Models\Product::imageUrl($product->image) }}"
                                        alt="{{ $product->name }}">
                                @endforelse
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="form-group col-md-6">
                                <label>Category</label>
                                <input type="text" class="form-control bg-white" value="{{ $product->category?->name }}"
                                    readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Subcategory</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ $product->subcategory?->name ?: 'Not provided' }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Product Name</label>
                                <input type="text" class="form-control bg-white" value="{{ $product->name }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Brand</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ $product->brand?->name ?: 'Not provided' }}" readonly>
                            </div>
                            @if ($product->model || $product->condition)
                                <div class="form-group col-md-6">
                                    <label>Model</label>
                                    <input type="text" class="form-control bg-white"
                                        value="{{ $product->model ?: 'Not provided' }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Condition</label>
                                    <input type="text" class="form-control bg-white"
                                        value="{{ $product->condition ? ucfirst($product->condition) : 'Not provided' }}"
                                        readonly>
                                </div>
                            @endif
                            <div class="form-group col-md-6">
                                <label>Cost Price</label>
                                <input type="text" class="form-control bg-white" value="{{ $product->cost_price }}"
                                    readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sale Price</label>
                                <input type="text" class="form-control bg-white" value="{{ $product->selling_price }}"
                                    readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>GST (%)</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ number_format((float) $product->gst_tax, 2) }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Price After GST</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ number_format($product->price_including_gst, 2) }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ $product->status ? 'Active' : 'Inactive' }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Note</label>
                                <input type="text" class="form-control bg-white"
                                    value="{{ $product->note ?: 'Not provided' }}" readonly>
                            </div>
                        </div>
                        <!-- end: Show Data -->
                        <div class="mt-2 text-center">
                            <a class="btn btn-primary mr-2" href="{{ route('products.edit', $product->id) }}">
                                <x-heroicon-o-pencil class="w-5 h-5 mr-1"
                                    style="display:inline-block; vertical-align:middle;" /> Edit
                            </a>
                            <a class="btn btn-primary" href="{{ route('products.index') }}">
                                <x-heroicon-o-arrow-left class="w-5 h-5 mr-1"
                                    style="display:inline-block; vertical-align:middle;" /> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
