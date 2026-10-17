@section('container')
    <style>
        .purchase-list-table th,
        .purchase-list-table td {
            font-size: 0.78rem;
            padding: 0.5rem 0.6rem;
            white-space: nowrap;
            vertical-align: middle;
        }

        .purchase-list-table td:nth-child(1) {
            white-space: normal;
        }

        .purchase-list-table .btn-sm,
        .purchase-list-table .btn.border {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 4px !important;
        }

        .purchase-list-table .btn-sm svg,
        .purchase-list-table .btn.border svg {
            width: 14px;
            height: 14px;
        }

        .purchase-list-table th {
            font-size: 8px;
            font-weight: 400;
            padding: 0.35rem 0.5rem;
            line-height: 1.2;
        }
    </style>


    @extends('dashboard.body.main')

@section('container')
    <div @class(['container-fluid'])>
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div @class(['row'])>
            <div @class(['col-lg-12'])>
                <div @class([
                    'd-flex',
                    'flex-wrap',
                    'align-items-center',
                    'justify-content-between',
                    'mb-4',
                ])>
                    <div>
                        <h4 @class(['mb-2'])>Purchase List</h4>
                        <p @class(['mb-0'])>Track incoming stock purchases and supplier payments.</p>
                    </div>
                    @can('create.sales')
                        <a href="{{ route('purchases.create') }}" @class(['btn', 'btn-primary', 'd-flex', 'align-items-center'])>
                            <x-heroicon-o-plus @class(['w-5', 'h-5', 'mr-1']) /> Add Purchase
                        </a>
                    @endcan
                </div>
            </div>

            <div @class(['col-lg-4', 'col-md-6'])>
                <div @class(['card', 'card-block', 'card-stretch', 'card-height'])>
                    <div @class([
                        'card-body',
                        'd-flex',
                        'align-items-center',
                        'justify-content-between',
                    ])>
                        <div>
                            <p @class(['mb-2'])>Total Purchases</p>
                            <h3 @class(['mb-0'])>PKR {{ number_format($totalPurchases, 2) }}</h3>
                        </div>
                        <span @class(['bg-primary', 'rounded', 'p-3'])>
                            <x-heroicon-o-shopping-bag @class(['w-7', 'h-7', 'text-white']) />
                        </span>
                    </div>
                </div>
            </div>
            <div @class(['col-lg-4', 'col-md-6'])>
                <div @class(['card', 'card-block', 'card-stretch', 'card-height'])>
                    <div @class([
                        'card-body',
                        'd-flex',
                        'align-items-center',
                        'justify-content-between',
                    ])>
                        <div>
                            <p @class(['mb-2'])>Items Received</p>
                            <h3 @class(['mb-0'])>{{ number_format($itemsReceived) }}</h3>
                        </div>
                        <span @class(['bg-success', 'rounded', 'p-3'])>
                            <x-heroicon-o-archive-box-arrow-down @class(['w-7', 'h-7', 'text-white']) />
                        </span>
                    </div>
                </div>
            </div>
            <div @class(['col-lg-4', 'col-md-6'])>
                <div @class(['card', 'card-block', 'card-stretch', 'card-height'])>
                    <div @class([
                        'card-body',
                        'd-flex',
                        'align-items-center',
                        'justify-content-between',
                    ])>
                        <div>
                            <p @class(['mb-2'])>Pending Payments</p>
                            <h3 @class(['mb-0'])>PKR {{ number_format($pendingPayments, 2) }}</h3>
                        </div>
                        <span @class(['bg-warning', 'rounded', 'p-3'])>
                            <x-heroicon-o-clock @class(['w-7', 'h-7', 'text-white']) />
                        </span>
                    </div>
                </div>
            </div>
            <div @class(['col-lg-12'])>
                <div @class(['card'])>
                    <div @class(['card-body'])>

                        <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                            <form action="{{ route('purchases.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                                <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                    <input type="search" name="search" class="form-control" placeholder="Search by purchase no, product, supplier, branch..." value="{{ request('search') }}">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                </button>
                                @if (request('search'))
                                <a href="{{ route('purchases.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </a>
                                @endif
                            </form>
                            @if (isset($purchases) && method_exists($purchases, 'total'))
                            <span class="badge badge-light border px-3 py-2">{{ $purchases->total() }} records</span>
                            @endif
                        </div>


                        {{-- Table --}}
                        <div @class(['table-responsive', 'rounded', 'mb-3'])>

                            <table @class(['table', 'mb-0', 'purchase-list-table'])>

                                <thead @class(['bg-white', 'text-uppercase'])>
                                    <tr @class(['ligth', 'ligth-data'])>
                                        <th>No.</th>
                                        <th>Purchase No</th>
                                        <th>Product Name</th>
                                        <th>Supplier</th>
                                        <th>Branch</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Paid Amount</th>
                                        <th>Due Amount</th>
                                        <th>Purchase Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody @class(['ligth-body']) style="font-size: 0.8rem;">

                                    @forelse ($purchases as $purchase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>{{ $purchase->purchase_no }}</td>

                                            <td class="truncate"
                                                title="{{ $purchase->items->pluck('product.name')->filter()->implode(', ') }}">
                                                {{ $purchase->items->pluck('product.name')->filter()->implode(', ') ?: '-' }}
                                            </td>

                                            <td>
                                                {{ $purchase->supplier->name ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $purchase->branch->name ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $purchase->items->sum('quantity') }}
                                            </td>

                                            <td>
                                                PKR {{ number_format($purchase->total_amount, 2) }}
                                            </td>

                                            <td>
                                                PKR {{ number_format($purchase->paid_amount, 2) }}
                                            </td>

                                            <td>
                                                PKR {{ number_format($purchase->due_amount, 2) }}
                                            </td>

                                            <td>
                                                {{ $purchase->purchase_date->format('Y-m-d') }}
                                            </td>

                                            <td>
                                                <span
                                                    class="badge
                                        badge-{{ $purchase->payment_status === 'paid'
                                            ? 'success'
                                            : ($purchase->payment_status === 'partial'
                                                ? 'warning'
                                                : 'danger') }}">
                                                    {{ ucfirst($purchase->payment_status) }}
                                                </span>
                                            </td>

                                            <td @class(['d-flex'])>

                                                <a href="{{ route('purchases.show', $purchase) }}"
                                                    class="btn btn-light btn-sm border mr-1" title="View">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>

                                                @can('edit.sales')
                                                    <a href="{{ route('purchases.edit', $purchase) }}"
                                                        class="btn btn-light btn-sm border mr-1" title="Edit">
                                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                                    </a>
                                                @endcan

                                                @can('create.sales')
                                                    <a href="{{ route('purchases.return.create', $purchase->id) }}"
                                                        class="btn btn-light btn-sm border  mr-1" title="Return">
                                                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                                                    </a>
                                                @endcan

                                                @can('delete.sales')
                                                    <form action="{{ route('purchases.destroy', $purchase) }}" method="POST"
                                                        class="delete-purchase-form"
                                                        onsubmit="return confirm('Are you sure you want to delete this purchase?');">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-light btn-sm border text-danger"
                                                            title="Delete">
                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                        </button>

                                                    </form>
                                                @endcan

                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-4">
                                                No purchases found.
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                            <div class="d-flex justify-content-end mt-3">
                                {{ $purchases->links() }}
                            </div>

                        </div>

                        <p class="text-muted mb-0">
                            Showing {{ $purchases->count() }}
                            of {{ $purchases->total() }}
                            purchase records.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
