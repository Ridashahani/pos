@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-2">Purchase Return #{{ $purchaseReturn->return_no }}</h4>
                <p class="mb-0">Details of this purchase return.</p>
            </div>
            <a href="{{ route('purchases.returns') }}" class="btn btn-primary border d-flex align-items-center">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" /> Back to List
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Purchase
                            No</strong><br>{{ $purchaseReturn->purchase->purchase_no ?? '-' }}</div>
                    <div class="col-md-3">
                        <strong>Supplier</strong><br>{{ $purchaseReturn->purchase->supplier->name ?? '-' }}
                    </div>
                    <div class="col-md-3"><strong>Return
                            Date</strong><br>{{ $purchaseReturn->return_date->format('Y-m-d') }}</div>
                    <div class="col-md-3">
                        <strong>Status</strong><br>
                        <span class="badge badge-{{ $purchaseReturn->status === 'approved' ? 'success' : 'warning' }}">
                            {{ ucfirst($purchaseReturn->status) }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12"><strong>Reason</strong><br>{{ $purchaseReturn->reason }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseReturn->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>PKR {{ number_format($item->unit_price, 2) }}</td>
                                    <td>PKR {{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
