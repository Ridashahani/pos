@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card card-block card-stretch">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">Add Stock Transfer</h4>
                        <a href="{{ route('stock.transfer') }}" class="btn btn-light">
                            <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" /> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('stock.transfer.store') }}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="product_id">Product / Item <span class="text-danger">*</span></label>
                                    <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                        <option value="">Select product from stock-in</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                                {{ $product->name }} ({{ $product->code }}) - {{ number_format($product->stock) }} available
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}"
                                        class="form-control @error('quantity') is-invalid @enderror" min="1" required>
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="from_branch_id">From Branch <span class="text-danger">*</span></label>
                                    <select id="from_branch_id" name="from_branch_id" class="form-control @error('from_branch_id') is-invalid @enderror" required>
                                        <option value="">Select source branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected(old('from_branch_id') == $branch->id)>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="to_branch_id">To Branch <span class="text-danger">*</span></label>
                                    <select id="to_branch_id" name="to_branch_id" class="form-control @error('to_branch_id') is-invalid @enderror" required>
                                        <option value="">Select destination branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected(old('to_branch_id') == $branch->id)>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1" /> Add Stock Transfer
                                </button>
                                <a href="{{ route('stock.transfer') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
