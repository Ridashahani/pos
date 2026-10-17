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

                        <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                            <form action="{{ route('purchases.returns') }}" method="GET" class="d-flex flex-wrap align-items-center">
                                <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                    <input type="search" name="search" class="form-control" placeholder="Search by return no, purchase no..." value="{{ request('search') }}">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                </button>
                                @if (request('search'))
                                <a href="{{ route('purchases.returns') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </a>
                                @endif
                            </form>
                            @if (isset($returns) && method_exists($returns, 'total'))
                            <span class="badge badge-light border px-3 py-2">{{ $returns->total() }} records</span>
                            @endif
                        </div>

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
