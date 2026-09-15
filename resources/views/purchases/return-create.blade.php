@extends('dashboard.body.main')

@section('container')
    <style>
        .return-page { background: #f8fafc; margin: -1.5rem; min-height: 100%; padding: 1.5rem; }
        .return-page .return-card { border: 1px solid #e5eaf0; border-radius: 10px; box-shadow: 0 2px 8px rgba(37, 52, 72, 0.04); }
        .return-page .return-card .card-body { padding: 1.1rem; }
        .return-page .page-kicker { color: #2f80ed; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .return-page .page-title { color: #1f2937; font-size: 1.25rem; font-weight: 600; margin-bottom: .2rem; }
        .return-page .page-subtitle, .return-page .field-label { color: #8490a0; font-size: .78rem; }
        .return-page .field-label { color: #536071; font-weight: 600; margin-bottom: .35rem; }
        .return-page .form-control { border-color: #dfe5ec; border-radius: 7px; font-size: .78rem; height: 36px; }
        .return-page textarea.form-control { height: 100px; }
        .return-page .purchase-info { background: #f5f8fb; border-radius: 8px; color: #536071; font-size: .78rem; padding: .8rem; }
        .return-page .purchase-info strong { color: #273142; }
        .return-page .return-table th { border-top: 0; border-bottom: 1px solid #364152; color: #687386; font-size: .72rem; padding: .4rem; }
        .return-page .return-table td { border-top: 0; color: #273142; font-size: .78rem; padding: .5rem .4rem; vertical-align: middle; }
        @media (max-width: 767.98px) { .return-page { margin: -1rem; padding: 1rem; } .return-page .return-table { min-width: 620px; } }
    </style>

    <div class="container-fluid">
        <div class="return-page">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <div class="page-kicker">Purchase Return</div>
                            <h4 class="page-title">Create Purchase Return</h4>
                            <p class="page-subtitle mb-0">Record the reason and items being returned to the supplier.</p>
                        </div>
                        <a href="{{ route('purchases.returns') }}" class="btn btn-light border d-flex align-items-center">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                        </a>
                    </div>
                </div>

                <div class="col-xl-8 mb-4 mb-xl-0">
                    <div class="card return-card h-100">
                        <div class="card-body">
                            <div class="purchase-info mb-4">
                                <div class="row">
                                    <div class="col-md-4"><strong>Purchase No</strong><br>{{ $purchase['number'] }}</div>
                                    <div class="col-md-4"><strong>Supplier</strong><br>{{ $purchase['supplier'] }}</div>
                                    <div class="col-md-4"><strong>Purchase Date</strong><br>{{ $purchase['date'] }}</div>
                                </div>
                            </div>
                            <div class="table-responsive rounded">
                                <table class="table return-table mb-0">
                                    <thead>
                                        <tr><th>#</th><th>Product</th><th>Purchased Qty</th><th>Return Qty</th><th>Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Purchase items from {{ $purchase['number'] }}</td>
                                            <td>{{ $purchase['items'] }}</td>
                                            <td><input type="number" class="form-control" value="1" min="1" max="{{ $purchase['items'] }}"></td>
                                            <td>PKR 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mb-0 mt-4">This static form does not save return records yet.</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card return-card">
                        <div class="card-body">
                            <div class="form-group">
                                <label class="field-label" for="return-date">Return Date <span class="text-danger">*</span></label>
                                <input id="return-date" type="date" class="form-control" value="2026-09-15">
                            </div>
                            <div class="form-group">
                                <label class="field-label" for="return-reason">Reason <span class="text-danger">*</span></label>
                                <textarea id="return-reason" class="form-control" placeholder="Enter return reason"></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label class="field-label" for="return-status">Status</label>
                                <select id="return-status" class="form-control">
                                    <option>Pending</option>
                                    <option>Approved</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-3"
                        onclick="alert('Purchase return submitted successfully. Static  data was not stored.')">Submit Return</button>
                </div>
            </div>
        </div>
    </div>
@endsection
