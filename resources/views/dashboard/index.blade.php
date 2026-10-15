@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <!-- Success Message -->
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- Banner / Welcome Section -->
            <div class="col-lg-12 mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h3 class="font-weight-bold">Dashboard Overview</h3>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="{{ route('pos.index') }}" class="btn btn-primary">
                            <x-heroicon-o-computer-desktop class="w-5 h-5 mr-2" />
                            Go to POS
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sales Summary Cards -->
            @php
                $summary = [
                    ['label' => "Today's Sales", 'value' => $today_sales, 'color' => 'primary', 'icon' => 'calendar'],
                    ['label' => 'Weekly Sales', 'value' => $weekly_sales, 'color' => 'info', 'icon' => 'calendar-days'],
                    ['label' => 'Monthly Sales', 'value' => $month_sales, 'color' => 'warning', 'icon' => 'chart-bar'],
                    [
                        'label' => 'Total Sales',
                        'value' => $total_sales,
                        'color' => 'success',
                        'icon' => 'currency-dollar',
                    ],
                ];
            @endphp

            @foreach ($summary as $item)
                <div class="col-12 col-sm-6 col-xl-3 mb-3 d-flex">
                    <div class="card w-100 h-100 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center card-total-sale">
                                <div class="icon iq-icon-box-2 bg-{{ $item['color'] }}-light">
                                    <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="w-6 h-6 text-{{ $item['color'] }}" />
                                </div>
                                <div>
                                    <p class="mb-2">{{ $item['label'] }}</p>
                                    <h4>PKR {{ number_format($item['value'], 2) }}</h4>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Metric Cards -->
            <div class="col-12 col-sm-6 col-xl-3 mb-3 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center card-total-sale">
                            <div class="icon iq-icon-box-2 bg-info-light">
                                <x-heroicon-o-banknotes class="w-6 h-6 text-info" />
                            </div>
                            <div>
                                <p class="mb-2">Total Paid</p>
                                <h4>PKR {{ number_format($total_paid, 2) }}</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 mb-3 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center card-total-sale">
                            <div class="icon iq-icon-box-2 bg-danger-light">
                                <x-heroicon-o-credit-card class="w-6 h-6 text-danger" />
                            </div>
                            <div>
                                <p class="mb-2">Total Due</p>
                                <h4>PKR {{ number_format($total_due, 2) }}</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 mb-3 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center card-total-sale">
                            <div class="icon iq-icon-box-2 bg-success-light">
                                <x-heroicon-o-check-circle class="w-6 h-6 text-success" />
                            </div>
                            <div>
                                <p class="mb-2">Complete Sales</p>
                                <h4>{{ $complete_sales }}</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 mb-3 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center card-total-sale">
                            <div class="icon iq-icon-box-2 bg-warning-light">
                                <x-heroicon-o-clock class="w-6 h-6 text-warning" />
                            </div>
                            <div>
                                <p class="mb-2">Pending Sales</p>
                                <h4>{{ $pending_sales }}</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="col-12 mb-4">
                <div class="card card-block card-stretch">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Monthly Sales Overview ({{ date('Y') }})</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="monthly-sales-chart" style="min-height: 250px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Recent Sales & Top Products -->
            <div class="col-12 col-xl-7 mb-4 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Recent Sales</h4>
                        </div>
                        <div class="card-header-toolbar d-flex align-items-center">
                            <a href="{{ route('sale.pendingSales') }}"
                                class="btn btn-outline-primary position-relative text-nowrap">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse($recent_sales as $sale)
                                        <tr>
                                            <td>{{ $sale->created_at->format('d M Y') }}</td>
                                            <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                                            <td>PKR {{ number_format($sale->grand_total, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $sale->status == 'completed' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($sale->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-sm"
                                                    href="{{ route('sale.saleDetails', $sale->id) }}">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No recent sales found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5 mb-4 d-flex">
                <div class="card w-100 h-100 mb-0">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Top Selling Products</h4>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th>Sold</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse($top_products as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td>{{ $product->product_code }}</td>
                                            <td><strong>{{ $product->total_sold }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No sales data yet.</td>
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

@section('specificpagescripts')
    <!-- ApexCharts -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof ApexCharts !== 'undefined') {
                var options = {
                    series: [{
                        name: 'Sales',
                        data: {!! $chart_data !!}
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        toolbar: {
                            show: false
                        },
                        parentHeightOffset: 0
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                            'Nov',
                            'Dec'
                        ],
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "PKR " + val
                            }
                        }
                    },
                    colors: ['#4788ff'],
                    fill: {
                        type: "gradient",
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.9,
                            stops: [0, 90, 100]
                        }
                    },
                    responsive: [{
                        breakpoint: 576,
                        options: {
                            chart: {
                                height: 250
                            },
                            xaxis: {
                                labels: {
                                    rotate: -45,
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        }
                    }]
                };

                var chart = new ApexCharts(document.querySelector("#monthly-sales-chart"), options);
                chart.render();
            } else {
                console.warn('ApexCharts library not found. Sales chart cannot be rendered.');
            }
        });
    </script>
@endsection
<style>
    .card-total-sale .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 56px;
        height: 56px;
        margin-right: .75rem;
    }

    .card-total-sale .icon svg {
        margin: 0;
    }

    .card-total-sale>div:last-child {
        min-width: 0;
    }

    .card-total-sale h4 {
        font-size: clamp(.85rem, 0.5rem + .8vw, 1.1rem);
        word-break: break-word;
    }

    .card-total-sale p {
        font-size: clamp(.75rem, .6rem + .4vw, .95rem);
        margin-bottom: .25rem !important;
    }

    .card-title {
        font-size: clamp(.95rem, .8rem + .5vw, 1.15rem);
    }

    .table td,
    .table th {
        font-size: clamp(.75rem, .65rem + .3vw, .9rem);
        white-space: nowrap;
    }

    /* Flex children ko shrink hone do, taake side scroll na aaye */
    .row>[class*="col-"] {
        min-width: 0;
    }

    .card,
    .card-body,
    .table-responsive {
        min-width: 0;
        max-width: 100%;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .content-page,
    .container-fluid {
        max-width: 100%;
        overflow-x: hidden;
    }

    @media (max-width: 575.98px) {
        .card-total-sale .icon {
            width: 44px;
            height: 44px;
            margin-right: .5rem;
        }

        .card-body {
            padding: .85rem;
        }

        .container-fluid {
            padding-left: .75rem;
            padding-right: .75rem;
        }
    }
</style>
