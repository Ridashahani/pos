@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-2">Purchase Returns</h4>
                        <p class="mb-0">List of returned purchases.</p>
                    </div>
                    <a href="{{ route('purchases.index') }}" class="btn btn-primary border d-flex align-items-center">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" /> Purchase List
                    </a>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        {{-- Search --}}
                        <form action="{{ route('purchases.returns') }}" method="GET"
                            class="d-flex flex-wrap align-items-center gap-3 mb-4">

                            <div class="d-flex align-items-center gap-2 ml-2">

                                <select name="search_by" class="form-control" style="width: 170px;">
                                    <option value="return_no" @selected(request('search_by', 'return_no') == 'return_no')>
                                        Return No
                                    </option>
                                    <option value="purchase_no" @selected(request('search_by') == 'purchase_no')>
                                        Purchase No
                                    </option>
                                </select>

                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="form-control ml-2 d-flex" placeholder="Search for..." style="width: 350px;">
                            </div>

                            <button type="submit" class="btn btn-outline-primary d-flex align-items-center ml-2">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 mr-1" />
                                Search
                            </button>

                            <a href="{{ route('purchases.returns') }}"
                                class="btn btn-outline-secondary d-flex align-items-center ml-2">
                                <x-heroicon-o-arrow-path class="w-4 h-4 mr-1" />
                                Reset
                            </a>
                        </form>

                        {{-- Table --}}
                        <div class="table-responsive rounded mb-3">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Return No</th>
                                        <th>Purchase No</th>
                                        <th>Supplier</th>
                                        <th>Return Date</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse ($returns as $return)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $return->return_no }}</td>
                                            <td>{{ $return->purchase->purchase_no ?? '-' }}</td>
                                            <td>{{ $return->purchase->supplier->name ?? '-' }}</td>
                                            <td>{{ $return->return_date->format('Y-m-d') }}</td>
                                            <td>{{ $return->returned_qty }}</td>
                                            <td>PKR {{ number_format($return->total_amount, 2) }}</td>
                                            <td>{{ Str::limit($return->reason, 40) }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $return->status === 'approved' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($return->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('purchases.returns.show', $return) }}"
                                                    class="btn btn-sm btn-light border" title="View">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">No returns found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
