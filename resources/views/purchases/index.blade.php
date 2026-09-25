@extends('dashboard.body.main')

@section('container')
    <div @class(['container-fluid'])>
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
                    <a href="{{ route('purchases.create') }}" @class(['btn', 'btn-primary', 'd-flex', 'align-items-center'])>
                        <x-heroicon-o-plus @class(['w-5', 'h-5', 'mr-1']) /> Add Purchase
                    </a>
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
                        <div @class(['table-responsive', 'rounded', 'mb-3'])>
                            <table @class(['table', 'mb-0'])>
                                <thead @class(['bg-white', 'text-uppercase'])>
                                    <tr @class(['ligth', 'ligth-data'])>
                                        <th>No.</th>
                                        <th>Purchase No</th>
                                        <th>Product Name</td>
                                        <th>Supplier</th>
                                        <th> Branch</th>
                                        <th>Purchase Date</th>
                                        <th>QTY</th>
                                        <th>Total</th>
                                        <th>Paid Amount</th>
                                        <th>Due Amount</th>

                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody @class(['ligth-body']) style="font-size: 0.8rem;">
                                    @forelse ($purchases as $purchase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $purchase->purchase_no }}</td>
                                            <td>{{ $purchase->purchase_no }}</td>

                                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                            <td>{{ $purchase->branch->name ?? '-' }}</td>
                                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                                            <td>{{ $purchase->items->sum('quantity') }}</td>
                                            <td>PKR {{ number_format($purchase->total_amount, 2) }}</td>

<td>PKR {{ number_format($purchase->paid_amount, 2) }}</td>
<td>PKR {{ number_format($purchase->due_amount, 2) }}</td>

                                            {{-- <td>
                                                <span
                                                    class="badge badge-{{ $purchase->status === 'received' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($purchase->status) }}
                                                </span>
                                            </td> --}}
                                            <td>
                                                <span
                                                    class="badge
                                    badge-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($purchase->payment_status) }}
                                                </span>
                                            </td>
                                            <td @class(['d-flex'])>
                                                <a href="{{ route('purchases.show', $purchase) }}"
                                                    class="btn btn-light btn-sm border mr-1" title="View">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>
                                                <a href="{{ route('purchases.edit', $purchase) }}"
                                                    class="btn btn-light btn-sm border mr-1" title="Edit">
                                                    <x-heroicon-o-pencil class="w-4 h-4" />
                                                </a>
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
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">No purchases found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-end mt-3">
                                {{ $purchases->links() }}
                            </div>
                        </div>
                        <p class="text-muted mb-0">Showing {{ $purchases->count() }} of {{ $purchases->total() }} purchase
                            records.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
