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

                <!-- Alert: Success Message -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Complete Sales List</h4>
                        <p class="mb-0">List of all completed sales. You can view details or reprint invoices.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <!-- Main Request Card -->
                <div class="card">
                    <div class="card-body">

                        <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                            <form action="{{ route('sale.completeSales') }}" method="GET" class="d-flex flex-wrap align-items-center">
                                <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                    <input type="search" name="search" class="form-control" placeholder="Search by invoice no, customer..." value="{{ request('search') }}">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                </button>
                                @if (request('search'))
                                <a href="{{ route('sale.completeSales') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </a>
                                @endif
                            </form>
                            @if (isset($orders) && method_exists($orders, 'total'))
                            <span class="badge badge-light border px-3 py-2">{{ $orders->total() }} records</span>
                            @endif
                        </div>

                        <!-- Sales Table -->
                        <div class="table-responsive rounded mb-3">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Invoice No</th>
                                        <th><x-sort-link name="customer.name" label="Name" /></th>
                                        <th><x-sort-link name="sale_date" label="Sale Date" /></th>
                                        <th>Payment</th>
                                        <th><x-sort-link name="total" label="Total" /></th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse ($sales as $sale)
                                        <tr>
                                            <td>{{ $sales->currentPage() * 10 - 10 + $loop->iteration }}</td>
                                            <td>{{ $sale->invoice_no }}</td>
                                            <td>{{ $sale->customer->name }}</td>
                                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                            <td>{{ $sale->payment_method }}</td>
                                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                                            <td>
                                                <span class="badge badge-success">{{ ucfirst($sale->status) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center list-action">
                                                    <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top"
                                                        title="Details" href="{{ route('sale.saleDetails', $sale->id) }}">
                                                        <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                                    </a>
                                                    <a class="btn btn-warning mr-2" data-toggle="tooltip"
                                                        data-placement="top" title="Print Invoice"
                                                        href="{{ route('sale.invoiceDownload', $sale->id) }}">
                                                        <x-heroicon-o-printer class="w-5 h-5 mr-0" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No complete sales found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
