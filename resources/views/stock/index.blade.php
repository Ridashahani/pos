@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <div>
                        <h4 class="mb-1">{{ $title }}</h4>
                        {{-- <p class="mb-0 text-muted">Review dummy stock activity for this inventory workflow.</p> --}}
                    </div>
                    {{-- <div class="d-flex align-items-center mt-3 mt-md-0">
                        <span class="badge bg-light text-primary px-3 py-2">Dummy data</span>
                    </div> --}}
                </div>
            </div>

            <div class="col-lg-3 col-md-2">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon iq-icon-box-2 bg-primary-light mr-3">
                            <x-heroicon-o-cube class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <p class="mb-1 text-muted">Total Products</p>
                            <h4 class="mb-0">{{ $total_products }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon iq-icon-box-2 bg-success-light mr-3">
                            <x-heroicon-o-chart-bar class="w-6 h-6 text-success" />
                        </div>
                        <div>
                            <p class="mb-1 text-muted">Total Units</p>
                            <h4 class="mb-0">{{ number_format($total_units) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon iq-icon-box-2 bg-warning-light mr-3">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-warning" />
                        </div>
                        <div>
                            <p class="mb-1 text-muted">This Month</p>
                            <h4 class="mb-0">{{ $this_month }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon iq-icon-box-2 bg-info-light mr-3">
                            <x-heroicon-o-clock class="w-6 h-6 text-info" />
                        </div>
                        <div>
                            <p class="mb-1 text-muted">{{ $type === 'stock-transfer' ? 'Pending Transfers' : 'Pending Items' }}</p>
                            <h4 class="mb-0">{{ $pending }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">{{ $title }} Records</h4>
                        @if ($type === 'stock-transfer')
                            <div class="d-flex align-items-center">
                                <form method="POST" action="{{ route('stock.transfer.clear') }}" class="mr-2"
                                    onsubmit="return confirm('Delete all stock transfer records? Stock will be returned.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Clear All</button>
                                </form>
                                <a href="{{ route('stock.transfer.create') }}" class="btn btn-primary">
                                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Stock Transfer
                                </a>
                            </div>
                        @elseif (in_array($type, ['stock-in', 'stock-out', 'sold-items', 'out-of-stock']))
                            <div class="d-flex align-items-center">
                                <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center mr-2">
                                    <input type="search" name="search" value="{{ request('search') }}"
                                        class="form-control mr-2" placeholder="Search product" aria-label="Search product">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                                @if (in_array($type, ['sold-items', 'out-of-stock']))
                                    <form method="POST" action="{{ route("stock.{$type}.clear") }}"
                                        onsubmit="return confirm('Delete all records on this page?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Clear All</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>Date</th>
                                        <th>Reference</th>
                                        @if ($type === 'stock-in')
                                            <th>Photo</th>
                                        @endif
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        @if ($type === 'stock-in')
                                            <th>Unit Cost</th>
                                            <th>Supplier</th>
                                            <th>Category</th>
                                        @elseif (in_array($type, ['stock-out', 'sold-items']))
                                            <th>Unit Buying Price</th>
                                            <th>Unit Sold Price</th>
                                            <th>Net Sold Price</th>
                                            <th>Destination</th>
                                            @if ($type === 'sold-items')
                                                <th>Status</th>
                                            @endif
                                        @elseif ($type === 'out-of-stock')
                                        @else
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Status</th>
                                        @endif
                                        @if ($type === 'stock-in')
                                            <th>Action</th>
                                        @elseif (in_array($type, ['stock-transfer', 'sold-items', 'out-of-stock']))
                                            <th class="text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td>{{ $row['date'] }}</td>
                                            <td><span class="font-weight-bold">{{ $row['reference'] }}</span></td>
                                            @if ($type === 'stock-in')
                                                <td>
                                                    <img class="avatar-60 rounded"
                                                        src="{{ $row['image'] ? asset('assets/images/product/' . $row['image']) : asset('assets/images/product/default.webp') }}"
                                                        alt="{{ $row['product'] }}">
                                                </td>
                                            @endif
                                            <td>{{ $row['product'] }}</td>
                                            <td>{{ number_format($row['quantity']) }}</td>
                                            @if ($type === 'stock-in')
                                                <td>{{ $row['currency'] ?? 'PKR' }} {{ number_format($row['unit_cost'], 2) }}</td>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td>{{ $row['category'] }}</td>
                                            @elseif (in_array($type, ['stock-out', 'sold-items']))
                                                <td>{{ $row['currency'] ?? 'PKR' }} {{ number_format($row['unit_buying_price'], 2) }}</td>
                                                <td>{{ $row['currency'] ?? 'PKR' }} {{ number_format($row['unit_price'], 2) }}</td>
                                                <td>{{ $row['currency'] ?? 'PKR' }} {{ number_format($row['net_sold_price'], 2) }}</td>
                                                <td>{{ $row['destination'] }}</td>
                                                @if ($type === 'sold-items')
                                                    <td><span class="badge {{ $row['status'] === 'returned' ? 'bg-warning' : 'bg-success' }}">{{ ucfirst($row['status']) }}</span></td>
                                                @endif
                                            @elseif ($type === 'out-of-stock')
                                            @else
                                                <td>{{ $row['from'] }}</td>
                                                <td>{{ $row['to'] }}</td>
                                                <td>
                                                    <span class="badge {{ $row['status'] === 'Completed' ? 'bg-success' : ($row['status'] === 'In Transit' ? 'bg-info' : 'bg-warning') }}">
                                                        {{ $row['status'] }}
                                                    </span>
                                                </td>
                                            @endif
                                            @if ($type === 'stock-in')
                                                <td>
                                                    <a href="{{ route('stock.in.details', $row['stock_in_id']) }}"
                                                        class="btn btn-info" data-toggle="tooltip" data-placement="top"
                                                        title="View details" aria-label="View details">
                                                        <x-heroicon-o-eye class="w-5 h-5" />
                                                    </a>
                                                </td>
                                            @elseif ($type === 'sold-items')
                                                <td class="text-center">
                                                    <a href="{{ route('sale.saleDetails', $row['sale_id']) }}"
                                                        class="btn btn-light btn-sm mr-1" title="Edit sale" aria-label="Edit sale">
                                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                    </a>
                                                    <form action="{{ route('stock.sold-items.destroy', $row['sold_item_id']) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Delete this sold item record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-light btn-sm" title="Delete sold item" aria-label="Delete sold item">
                                                            <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                                        </button>
                                                    </form>
                                                </td>
                                            @elseif ($type === 'out-of-stock')
                                                <td class="text-center">
                                                    <a href="{{ route('purchases.create', ['product_id' => $row['product_id']]) }}"
                                                        class="btn btn-primary btn-sm mr-1">Purchase again</a>
                                                    <a href="{{ route('products.edit', $row['product_id']) }}" class="btn btn-light btn-sm mr-1" title="Edit product" aria-label="Edit product">
                                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                    </a>
                                                    <form action="{{ route('stock.out-of-stock.destroy', $row['stock_in_id']) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Delete this exhausted stock-in record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-light btn-sm" title="Delete stock-in record" aria-label="Delete stock-in record">
                                                            <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                                        </button>
                                                    </form>
                                                </td>
                                            @elseif ($type === 'stock-transfer')
                                                <td class="text-center">
                                                    @if (!empty($row['id']))
                                                        <a href="{{ route('stock.transfer.edit', $row['id']) }}"
                                                            class="btn btn-light btn-sm mr-1" title="Edit stock transfer"
                                                            aria-label="Edit stock transfer">
                                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                        </a>
                                                        <form action="{{ route('stock.transfer.destroy', $row['id']) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-light btn-sm" title="Delete stock transfer"
                                                                aria-label="Delete stock transfer">
                                                                <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No stock records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if (in_array($type, ['stock-in', 'stock-out', 'sold-items', 'out-of-stock']))
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="text-muted small">{{ $total_products }} matching records</span>
                            {{ $pagination->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
