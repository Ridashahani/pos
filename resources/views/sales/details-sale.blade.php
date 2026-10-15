@extends('dashboard.body.main')

@section('container')
    @php
        $sale = $sale ?? $sale;
        $saleDetails = $saleDetails;
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->has('error'))
                    <div class="alert alert-danger" role="alert">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-1 inline" />
                        {{ $errors->first('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" />
                        {{ session('success') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Sales Details Information</h4>
                        </div>
                        <div>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                                <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Customer Profile Info -->
                        <div class="d-flex align-items-center mb-4">
                            <div>
                                <h5 class="mb-1">{{ $sale->customer->name }}</h5>
                                <p class="mb-0 text-muted">{{ $sale->customer->email }}</p>
                                <p class="mb-0 text-muted">{{ $sale->customer->address }}</p>
                            </div>
                        </div>

                        <!-- Sale Information Form (Read Only) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" class="form-control bg-white" value="{{ $sale->customer->name }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer Phone</label>
                                    <input type="text" class="form-control bg-white" value="{{ $sale->customer->phone }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sales Date</label>
                                    <input type="text" class="form-control bg-white"
                                        value="{{ $sale->sale_date->format('Y-m-d') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sales Invoice</label>
                                    <input class="form-control bg-white" value="{{ $sale->invoice_no }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Type</label>
                                    <input class="form-control bg-white" value="{{ $sale->payment_method }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paid Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">PKR</span>
                                        </div>
                                        <input type="text" class="form-control bg-white"
                                            value="{{ number_format($sale->paid_amount, 2) }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">PKR</span>
                                        </div>
                                        <input type="text" class="form-control bg-white"
                                            value="{{ number_format($sale->due_amount, 2) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions for Pending Sales -->
                        @if ($sale->status == 'pending')

                            @if ($sale->due_amount > 0)
                                {{-- Due Amount Warning Banner --}}
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="card border-0 mb-0"
                                            style="background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%); border-left: 4px solid #ffc107 !important; border-radius: 8px;">
                                            <div class="card-body py-3 px-4">
                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3"
                                                            style="width:42px; height:42px; background:#ffc107; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-white" />
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold text-dark">Due Payment Pending</h6>
                                                            <p class="mb-0 text-muted" style="font-size:0.875rem;">
                                                                PKR <strong
                                                                    class="text-danger">{{ number_format($sale->due_amount, 2) }}</strong>
                                                                is still outstanding. Pay the due amount to unlock sale completion.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center mt-2 mt-sm-0">
                                                        <a class="btn btn-outline-secondary btn-sm mr-2"
                                                            href="{{ route('sale.pendingSales') }}">
                                                            <x-heroicon-o-x-mark class="w-4 h-4 mr-1 inline" /> Cancel
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm mr-2"
                                                            data-toggle="modal" data-target="#pay-due-modal-detail">
                                                            <x-heroicon-o-banknotes class="w-4 h-4 mr-1 inline" /> Pay Due Amount
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm" disabled
                                                            title="Clear the due amount first."
                                                            style="opacity:0.5; cursor:not-allowed;">
                                                            <x-heroicon-o-lock-closed class="w-4 h-4 mr-1 inline" /> Complete Sale
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- No due — allow completion --}}
                                <div class="row mt-4">
                                    <div class="col-lg-12 d-flex justify-content-end">
                                        <form action="{{ route('sale.updateStatus') }}" method="POST" class="d-inline">
                                            @method('put')
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $sale->id }}">
                                            <a class="btn btn-outline-secondary btn-sm mr-2"
                                                href="{{ route('sale.pendingSales') }}">
                                                <x-heroicon-o-x-mark class="w-4 h-4 mr-1 inline" /> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Are you sure you want to complete this sale? This reduces stock.')">
                                                <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Complete Sale
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-success text-center" role="alert">
                                        <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> This Sale is completed.
                                    </div>
                                    <form action="{{ route('sale.return', $sale->id) }}" method="POST" class="text-right">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('Return all items from this sale to stock?')">
                                            Return Sale
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sale Items Table -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Sale Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table class="table mb-0">
                                <thead class="bg-light text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Photo</th>
                                        <th>Product Name</th>
                                        <th>Product Code</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @foreach ($saleDetails as $item)
                                        <tr id="item-{{ $item->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <img class="avatar-50 rounded"
                                                    src="{{ \App\Models\Product::imageUrl($item->product?->image) }}"
                                                    alt="{{ $item->product->name ?? 'Product' }}"
                                                    style="object-fit: cover;">
                                            </td>
                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                            <td>{{ $item->product->code ?? 'N/A' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">Subtotal</td>
                                        <td class="font-weight-bold">{{ number_format($sale->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">Tax</td>
                                        <td class="font-weight-bold">{{ number_format($sale->tax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold text-primary"
                                            style="font-size: 1.1em;">
                                            Total</td>
                                        <td class="font-weight-bold text-primary" style="font-size: 1.1em;">
                                            {{ number_format($sale->grand_total, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Pay Due Modal --}}
@if ($sale->status == 'pending' && $sale->due_amount > 0)
<div class="modal fade" id="pay-due-modal-detail" tabindex="-1" role="dialog" aria-labelledby="payDueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payDueModalLabel">
                    <x-heroicon-o-banknotes class="w-5 h-5 mr-1 inline" /> Pay Due Amount
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sale.updateDue') }}" method="POST">
                @csrf
                @method('post')
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-3 p-3 rounded" style="background:#f8f9fa;">
                        <span class="text-muted">Total Due:</span>
                        <strong class="text-danger">PKR {{ number_format($sale->due_amount, 2) }}</strong>
                    </div>
                    <div class="form-group">
                        <label for="due_amount_detail">Amount to Pay <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">PKR</span>
                            </div>
                            <input type="number" class="form-control" name="due_amount"
                                id="due_amount_detail" step="0.01" min="0.01"
                                max="{{ $sale->due_amount }}"
                                value="{{ $sale->due_amount }}" required>
                        </div>
                        <small class="text-muted">Enter amount being paid now (max: PKR {{ number_format($sale->due_amount, 2) }})</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1 inline" /> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
