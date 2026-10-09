@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card card-block card-stretch">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">{{ isset($transfer) ? 'Edit Stock Transfer' : 'Add Stock Transfer' }}</h4>
                        <a href="{{ route('stock.transfer') }}" class="btn btn-light">
                            <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" /> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ isset($transfer) ? route('stock.transfer.update', $transfer) : route('stock.transfer.store') }}">
                            @csrf
                            @isset($transfer)
                                @method('PUT')
                            @endisset
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="stock_in_id">Stock-In Item <span class="text-danger">*</span></label>
                                    <select id="stock_in_id" name="stock_in_id" class="form-control @error('stock_in_id') is-invalid @enderror" required>
                                        <option value="">Select stock-in item and source branch</option>
                                        @foreach ($stockIns as $stockIn)
                                            @php
                                                $variationLabel = collect($stockIn->variation_details ?? [])
                                                    ->map(fn (array $variation): string => trim(($variation['name'] ?? '') . ': ' . ($variation['value'] ?? ''), ': '))
                                                    ->filter()
                                                    ->implode(', ');
                                                $variationLabel = $variationLabel ?: ($stockIn->variation?->name ?? '');
                                            @endphp
                                            @php
                                                $availableQuantity = $stockIn->remaining_quantity
                                                    + (isset($transfer) && (int) $transfer->stock_in_id === (int) $stockIn->id ? $transfer->quantity : 0);
                                            @endphp
                                            <option value="{{ $stockIn->id }}" @selected(old('stock_in_id', $transfer->stock_in_id ?? '') == $stockIn->id)>
                                                {{ $stockIn->product->name }}{{ $variationLabel ? ' - ' . $variationLabel : '' }} | {{ $stockIn->branch->name }} | {{ number_format($availableQuantity) }} available
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('stock_in_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if ($stockIns->isEmpty())
                                        <div class="text-danger mt-1">Stock is not available in any branch.</div>
                                    @endif
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $transfer->quantity ?? '') }}"
                                        class="form-control @error('quantity') is-invalid @enderror" min="1" required>
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="to_branch_id">To Branch <span class="text-danger">*</span></label>
                                    <select id="to_branch_id" name="to_branch_id" class="form-control @error('to_branch_id') is-invalid @enderror" required>
                                        <option value="">Select destination branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected(old('to_branch_id', $transfer->to_branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1" /> {{ isset($transfer) ? 'Update Stock Transfer' : 'Add Stock Transfer' }}
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
