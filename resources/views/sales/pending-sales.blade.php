@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0; /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        @media (min-width: 576px) {
            .row-selector-container {
                min-width: 180px; /* Apply min-width only on sm+ screens */
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
                        <h4 class="mb-3">Pending Sales List</h4>
                        <p class="mb-0">Sales that are currently pending. You can view details to complete them.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <!-- Main Card -->
                <div class="card">
                    <div class="card-body">

                        <!-- Filter Form -->
                        <form action="{{ route('sale.pendingSales') }}" method="get">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <!-- Row Selector -->
                                <div class="form-group mb-0 mr-2 mt-n3 row-selector-container">
                                    <div class="d-flex align-items-center">
                                        <label for="row" class="mb-0 mr-2" style="min-width: 50px;">Row:</label>
                                        <select class="form-control" name="row">
                                            <option value="10" @if (request('row') == '10') selected="selected" @endif>10
                                            </option>
                                            <option value="25" @if (request('row') == '25') selected="selected" @endif>25
                                            </option>
                                            <option value="50" @if (request('row') == '50') selected="selected" @endif>50
                                            </option>
                                            <option value="100" @if (request('row') == '100') selected="selected" @endif>100
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Search Input -->
                                <div class="form-group row">
                                    <label class="control-label col-sm-3 align-self-center" for="search">Search:</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="text" id="search" class="form-control" name="search" placeholder="Search sale"
                                                value="{{ request('search') }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="input-group-text bg-primary">
                                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

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
                                            <td>{{ (($sales->currentPage() * 10) - 10) + $loop->iteration }}</td>
                                            <td>{{ $sale->invoice_no }}</td>
                                            <td>{{ $sale->customer->name }}</td>
                                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                            <td>{{ $sale->payment_type }}</td>
                                            <td>{{ number_format($sale->total, 2) }}</td>
                                            <td>
                                                <span class="badge badge-warning">{{ ucfirst($sale->sale_status) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center list-action">
                                                    <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top" title="Details"
                                                        href="{{ route('sale.saleDetails', $sale->id) }}">
                                                        <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No pending sales found.</td>
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
