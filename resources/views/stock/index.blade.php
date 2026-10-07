@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

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
                            <div class="d-flex flex-wrap align-items-center">
                                <form method="GET" action="{{ url()->current() }}"
                                    class="form-group row align-items-center mb-0 mr-2" style="width: 300px; max-width: 100%;">
                                    <div class="col-sm-12">
                                        <div class="input-group">
                                            <input type="search" id="search" name="search" value="{{ request('search') }}"
                                                class="form-control" placeholder="Search product" aria-label="Search product">
                                            <div class="input-group-append">
                                                <button type="submit" class="input-group-text bg-primary" aria-label="Search">
                                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <a href="{{ route('stock.transfer.create') }}" class="btn btn-primary mr-2">
                                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Stock Transfer
                                </a>
                                <form method="POST" action="{{ route('stock.transfer.clear') }}"
                                    onsubmit="return confirm('Delete all stock transfer records? Stock will be returned.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Clear All</button>
                                </form>
                            </div>
                        @elseif (in_array($type, ['stock-in', 'stock-out', 'sold-items', 'out-of-stock']))
                            <div class="d-flex align-items-center">
                                <form method="GET" action="{{ url()->current() }}"
                                    class="form-group row align-items-center mb-0 mr-2" style="width: 300px; max-width: 100%;"
                                    @if ($type === 'stock-in') id="stock-in-search-form" @endif>
                                    <div class="col-sm-12">
                                        <div class="input-group">
                                            <input type="search" name="search" value="{{ request('search') }}"
                                                @if ($type === 'stock-in') id="stock-in-search" @else id="search" @endif
                                                class="form-control" placeholder="Search product" aria-label="Search product">
                                            <div class="input-group-append">
                                                <button type="submit" class="input-group-text bg-primary" aria-label="Search">
                                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                @if (in_array($type, ['sold-items', 'out-of-stock']))
                                    <form method="POST" action="{{ route("stock.{$type}.clear") }}"
                                        onsubmit="return confirm('{{ $type === 'out-of-stock' ? 'Delete all out-of-stock records? Records linked to sales or transfers will be skipped.' : 'Delete all sold item records?' }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Clear All</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if ($type === 'stock-in')
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="bg-white text-uppercase">
                                        <tr class="ligth ligth-data">
                                            <th>No</th>
                                            <th>Product_Name</th>
                                            <th>Variation</th>
                                            <th>Branch</th>
                                            <th>Average Unit Cost</th>
                                            <th class="text-right text-nowrap">Available Qty</th>
                                            <th>Batches</th>
                                            <th>Low Stock</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="ligth-body">
                                        @forelse ($rows as $row)
                                            <tr>
                                                <td>{{ $pagination->firstItem() + $loop->index }}</td>
                                                <td>{{ $row['product'] }}</td>
                                                <td>{{ $row['variation'] }}</td>
                                                <td>{{ $row['branch'] }}</td>
                                                <td>{{ $row['currency'] }} {{ number_format($row['cost_price'], 2) }}</td>
                                                <td class="text-right">
                                                    <span class="badge {{ $row['remaining_quantity'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($row['remaining_quantity']) }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($row['batch_count']) }}</td>
                                                <td>
                                                    @if ($row['remaining_quantity'] < 5)
                                                        <span class="text-danger d-inline-flex align-items-center">
                                                            <x-heroicon-o-x-circle class="w-5 h-5 mr-1" /> Low Stock
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('stock.in.details', $row['stock_in_id']) }}"
                                                        class="btn btn-info btn-sm d-inline-flex align-items-center" title="View batch details" aria-label="View batch details">
                                                        <x-heroicon-o-eye class="w-4 h-4 mr-1" /> View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">No stock records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
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
                                        @if (in_array($type, ['out-of-stock', 'stock-transfer']))
                                            <th>Variation</th>
                                        @endif
                                        @if ($type === 'out-of-stock')
                                            <th>Branch</th>
                                        @endif
                                        <th>Quantity</th>
                                        @if ($type === 'stock-in')
                                            <th>Unit Cost</th>
                                            <th>Supplier</th>
                                            <th>Category</th>
                                        @elseif (in_array($type, ['stock-out', 'sold-items']))
                                            <th>Unit Buying Price</th>
                                            <th>Unit Sold Price</th>
                                            <th>Net Sold Price</th>
                                            <th>{{ $type === 'sold-items' ? 'Branch' : 'Destination' }}</th>
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
                                            <td>
                                                @if ($type === 'sold-items')
                                                    <span class="font-weight-bold">{{ $row['reference'] ?: 'Sale' }}</span>
                                                @else
                                                    <span class="font-weight-bold">{{ $row['reference'] }}</span>
                                                @endif
                                            </td>
                                            @if ($type === 'stock-in')
                                                <td>
                                                    <img class="avatar-60 rounded"
                                                        src="{{ $row['image'] ? asset('assets/images/product/' . $row['image']) : asset('assets/images/product/default.webp') }}"
                                                        alt="{{ $row['product'] }}">
                                                </td>
                                            @endif
                                            <td>{{ $row['product'] }}</td>
                                            @if (in_array($type, ['out-of-stock', 'stock-transfer']))
                                                <td>{{ $row['variation'] }}</td>
                                            @endif
                                            @if ($type === 'out-of-stock')
                                                <td>{{ $row['branch'] }}</td>
                                            @endif
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
                                                    <details class="text-left">
                                                        <summary class="btn btn-light btn-sm border">Transactions ({{ $row['transactions']->count() }})</summary>
                                                        <div class="bg-white border rounded p-2 mt-1 position-absolute" style="z-index: 2; min-width: 220px;">
                                                            @foreach ($row['transactions'] as $transaction)
                                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                                    <a href="{{ route('sale.saleDetails', $transaction->sale_id) }}">
                                                                        {{ $transaction->sale?->invoice_no ?: 'Sale #' . $transaction->sale_id }}
                                                                    </a>
                                                                    <form action="{{ route('stock.sold-items.destroy', $transaction) }}" method="POST"
                                                                        onsubmit="return confirm('Delete this sold item record?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-light btn-sm" title="Delete sold item" aria-label="Delete sold item">
                                                                            <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                </td>
                                            @elseif ($type === 'out-of-stock')
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-nowrap align-items-center justify-content-center">
                                                        <a href="{{ route('purchases.create', ['product_id' => $row['product_id']]) }}"
                                                            class="btn btn-primary btn-sm d-inline-flex align-items-center mr-1 px-2 py-1"
                                                            title="Purchase this product again">
                                                            Buy again
                                                        </a>
                                                        @can('access.products')
                                                            <a href="{{ route('products.edit', $row['product_id']) }}"
                                                                class="btn btn-light btn-sm d-inline-flex align-items-center mr-1 px-2 py-1"
                                                                title="Edit product" aria-label="Edit product">
                                                                <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" /> Edit
                                                            </a>
                                                        @endcan
                                                        <form action="{{ route('stock.out-of-stock.destroy', $row['stock_in_id']) }}" method="POST"
                                                            class="d-inline-flex"
                                                            onsubmit="return confirm('Delete all exhausted batches for this product, variation, and branch? Batches linked to sales or transfers will be kept.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-light btn-sm d-inline-flex align-items-center px-2 py-1"
                                                                title="Delete exhausted batches" aria-label="Delete exhausted batches">
                                                                <x-heroicon-o-trash class="w-4 h-4 text-danger mr-1" /> Delete
                                                            </button>
                                                        </form>
                                                    </div>
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
                                            <td colspan="{{ $type === 'stock-transfer' ? 9 : 8 }}" class="text-center text-muted py-4">No stock records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    @if (isset($pagination) && $pagination->total() > 10)
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="text-muted small">{{ $total_products }} matching records</span>
                            {{ $pagination->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if ($type === 'stock-in')
        <script>
            document.getElementById('stock-in-search')?.addEventListener('search', function () {
                if (this.value.trim() === '') {
                    document.getElementById('stock-in-search-form')?.requestSubmit();
                }
            });
        </script>
    @endif
@endsection
