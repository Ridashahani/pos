@extends('dashboard.body.main')

@section('container')
<style>
    .module-toolbar .form-control,
    .module-toolbar .btn {
        height: 42px;
    }

    .module-table th {
        color: #718096;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .module-table thead.bg-primary th {
        color: #fff;
    }

    .module-table td {
        color: #1f2937;
        vertical-align: middle;
    }

    .account-balance-card {
        border-radius: 10px;
        padding: 18px 20px;
        color: #fff;
    }

    .account-balance-card.jazzcash {
        background: linear-gradient(135deg, #d0006f, #770d0d);
    }

    .account-balance-card.easypaisa {
        background: linear-gradient(135deg, #00a651, #046b36);
    }

    .account-balance-card.bank {
        background: linear-gradient(135deg, #1e3a8a, #1e293b);
    }

    .account-balance-card .acc-badge {
        font-size: 11px;
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-2">Payments</h4>
                    <p class="mb-0 text-muted">Money transfer &amp; cash withdrawal transactions.</p>
                </div>
                <a href="{{ route('payments.create') }}" class="btn btn-primary add-list d-flex align-items-center mt-3 mt-md-0">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Transaction
                </a>
            </div>
        </div>

        <div class="col-lg-12">
            @if (session('success'))
            <div class="alert text-white bg-success" role="alert">
                <div class="iq-alert-text">{{ session('success') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            @endif
        </div>

        {{-- Accounts overview --}}
        <div class="col-lg-12">
            <div class="row">
                @foreach ($accounts as $acc)
                <div class="col-md-4">
                    <div class="account-balance-card {{ $acc->type }} mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold">{{ $acc->name }}</span>
                            <span class="acc-badge">{{ ucfirst($acc->type) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <p class="mb-0" style="font-size:11px;opacity:.85;">Opening</p>
                                <strong>Rs {{ number_format($acc->opening_balance, 0) }}</strong>
                            </div>
                            <div>
                                <p class="mb-0" style="font-size:11px;opacity:.85;">Current Balance</p>
                                <strong style="font-size:18px;">Rs {{ number_format($acc->balance, 0) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Summary stats --}}
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-block card-stretch card-height mb-4">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Transactions</p>
                            <h5 class="mb-0">{{ $summary['total_transactions'] }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-block card-stretch card-height mb-4">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Sent</p>
                            <h5 class="mb-0">Rs {{ number_format($summary['total_sent'], 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-block card-stretch card-height mb-4">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Withdrawals</p>
                            <h5 class="mb-0">Rs {{ number_format($summary['total_withdrawals'], 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-block card-stretch card-height mb-4">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Commission (Profit)</p>
                            <h5 class="mb-0 text-success">Rs {{ number_format($summary['total_commission'], 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions table --}}
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                        <form action="{{ route('payments.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                            <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                <input type="search" name="search" class="form-control" placeholder="Search customer....." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </button>
                            @if (request('search'))
                            <a href="{{ route('payments.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </a>
                            @endif
                        </form>
                        <span class="badge badge-light border px-3 py-2">{{ $transactions->total() }} records</span>
                    </div>

                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Time</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Commission</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $tx)
                                <tr>
                                    <td>{{ $tx->transaction_date->format('d M, h:i A') }}</td>
                                    <td>{{ $tx->customer_name }}</td>
                                    <td>
                                        <span class="badge {{ $tx->type === 'send' ? 'badge-primary' : 'badge-success' }}">
                                            {{ $tx->type === 'send' ? 'Send Money' : 'Cash Withdrawal' }}
                                        </span>
                                    </td>
                                    <td>{{ $tx->account->name ?? '—' }}</td>
                                    <td><strong>{{ number_format($tx->amount, 2) }}</strong></td>
                                    <td>{{ number_format($tx->commission, 2) }}</td>
                                    <td>{{ $tx->description ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('payments.edit', $tx) }}" class="btn btn-light btn-sm mr-1" title="Edit transaction">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </a>
                                        <form action="{{ route('payments.destroy', $tx) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm" title="Delete transaction">
                                                <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection