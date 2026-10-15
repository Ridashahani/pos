@extends('dashboard.body.main')

@section('container')
    <style>
        .return-page {
            background: #f8fafc;
            margin: -1.5rem;
            min-height: 100%;
            padding: 1.5rem;
        }

        .return-page .return-card {
            border: 1px solid #e5eaf0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(37, 52, 72, 0.04);
        }

        .return-page .return-card .card-body {
            padding: 1.1rem;
        }

        .return-page .page-kicker {
            color: #2f80ed;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .return-page .page-title {
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: .2rem;
        }

        .return-page .page-subtitle,
        .return-page .field-label {
            color: #8490a0;
            font-size: .78rem;
        }

        .return-page .field-label {
            color: #536071;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .return-page .form-control {
            border-color: #dfe5ec;
            border-radius: 7px;
            font-size: .78rem;
            height: 36px;
        }

        .return-page textarea.form-control {
            height: 100px;
        }

        .return-page .purchase-info {
            background: #f5f8fb;
            border-radius: 8px;
            color: #536071;
            font-size: .78rem;
            padding: .8rem;
        }

        .return-page .purchase-info strong {
            color: #273142;
        }

        .return-page .return-table th {
            border-top: 0;
            border-bottom: 1px solid #364152;
            color: #687386;
            font-size: .72rem;
            padding: .4rem;
        }

        .return-page .return-table td {
            border-top: 0;
            color: #273142;
            font-size: 10px;
            padding: .5rem .4rem;
            vertical-align: middle;
        }

        .return-page .qty-disabled {
            background: #f1f3f5;
            color: #adb5bd;
        }

        @media (max-width: 767.98px) {
            .return-page {
                margin: -1rem;
                padding: 1rem;
            }

            .return-page .return-table {
                min-width: 620px;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="return-page">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('purchases.return.store', $purchase) }}">
                @csrf

                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <div class="page-kicker">Purchase Return</div>
                                <h4 class="page-title">Create Purchase Return</h4>
                                <p class="page-subtitle mb-0">Record the reason and items being returned to the supplier.
                                </p>
                            </div>
                            <a href="{{ route('purchases.returns') }}"
                                class="btn btn-light border d-flex align-items-center">
                                <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                            </a>
                        </div>
                    </div>

                    <div class="col-xl-8 mb-4 mb-xl-0">
                        <div class="card return-card h-100">
                            <div class="card-body">
                                <div class="purchase-info mb-4">
                                    <div class="row">
                                        <div class="col-md-4"><strong>Purchase No</strong><br>{{ $purchase->purchase_no }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Supplier</strong><br>{{ $purchase->supplier->name ?? '-' }}
                                        </div>
                                        <div class="col-md-4"><strong>Purchase
                                                Date</strong><br>{{ $purchase->purchase_date->format('Y-m-d') }}</div>
                                    </div>
                                </div>
                                <div class="table-responsive rounded">
                                    <table class="table return-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Purchased Qty</th>
                                                <th>Already Returned</th>
                                                <th>Return Qty</th>
                                                <th>Unit Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchase->items as $item)
                                                @php
                                                    $already = (int) ($returned[$item->id] ?? 0);
                                                    $available = $item->quantity - $already;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->product->name ?? '-' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $already }}</td>
                                                    <td>
                                                        <input type="number" name="return_qty[{{ $item->id }}]"
                                                            class="form-control @if ($available <= 0) qty-disabled @endif"
                                                            value="{{ old("return_qty.{$item->id}", 0) }}" min="0"
                                                            max="{{ $available }}"
                                                            @if ($available <= 0) disabled @endif>
                                                        @if ($available <= 0)
                                                            <small class="text-muted">Fully returned</small>
                                                        @else
                                                            <small class="text-muted">Max {{ $available }}</small>
                                                        @endif
                                                    </td>
                                                    <td>PKR {{ number_format($item->unit_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card return-card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="field-label" for="return-date">Return Date <span
                                            class="text-danger">*</span></label>
                                    <input id="return-date" name="return_date" type="date" class="form-control"
                                        value="{{ old('return_date', date('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="field-label" for="return-reason">Reason <span
                                            class="text-danger">*</span></label>
                                    <textarea id="return-reason" name="reason" class="form-control" placeholder="Enter return reason">{{ old('reason') }}</textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="field-label" for="return-status">Status</label>
                                    <select id="return-status" name="status" class="form-control">
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>
                                            Approved</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-3">Submit Return</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
