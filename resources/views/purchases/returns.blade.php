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
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $purchase['number'] }}</td>
                                            <td>{{ $purchase['supplier'] }}</td>
                                            <td>{{ $purchase['date'] }}</td>
                                            <td>{{ $purchase['items'] }}</td>
                                            <td>{{ $purchase['total'] }}</td>
                                            <td>{{ $purchase['reason'] }}</td>
                                            <td><span class="badge badge-success">Return</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mb-0">These are the same static purchase records shown in the purchase list.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
