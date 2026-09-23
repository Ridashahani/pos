@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-2">Purchase List</h4>
                        <p class="mb-0">Track incoming stock purchases and supplier payments.</p>
                    </div>
                    <a href="{{ route('purchases.create') }}" class="btn btn-primary d-flex align-items-center">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Purchase
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2">Total Purchases</p>
                            <h3 class="mb-0">PKR 24,850.00</h3>
                        </div>
                        <span class="bg-primary rounded p-3">
                            <x-heroicon-o-shopping-bag class="w-7 h-7 text-white" />
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2">Items Received</p>
                            <h3 class="mb-0">1,248</h3>
                        </div>
                        <span class="bg-success rounded p-3">
                            <x-heroicon-o-archive-box-arrow-down class="w-7 h-7 text-white" />
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2">Pending Payments</p>
                            <h3 class="mb-0">PKR 5,420.00</h3>
                        </div>
                        <span class="bg-warning rounded p-3">
                            <x-heroicon-o-clock class="w-7 h-7 text-white" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive rounded mb-3">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Purchase No</th>
                                        <th>Supplier</th>
                                        <th>Purchase Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $purchase->purchase_number }}</td>
                                            <td>{{ $purchase->supplier?->name ?: 'N/A' }}</td>
                                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                                            <td>{{ $purchase->items->sum('quantity') }}</td>
                                            <td>PKR {{ number_format($purchase->total_amount, 2) }}</td>
                                            <td>{{ $purchase->amount_paid >= $purchase->total_amount ? 'Paid' : 'Due' }}</td>
                                            <td>
                                                <span class="badge {{ $purchase->payment_status === 'Paid' ? 'badge-success' : 'badge-warning' }}">{{ $purchase->payment_status }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('purchases.return.create', $purchase->purchase_number) }}"
                                                    class="btn btn-sm btn-outline-danger ml-2">Return</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mb-0">Purchases are added to stock-in when they are saved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
