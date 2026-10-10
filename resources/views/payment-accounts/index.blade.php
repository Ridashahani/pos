@extends('dashboard.body.main')

@section('container')
<style>
    .payment-page-title {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.2;
    }

    .payment-account-section-title {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
    }

    .payment-account-table thead.bg-primary th {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        padding: 12px 10px;
        vertical-align: middle;
    }

    .payment-account-table td {
        color: #1f2937;
        vertical-align: middle;
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="payment-page-title mb-2">Payment Accounts</h4>
            <p class="mb-0 text-muted">Manage the shop owner's JazzCash, Easypaisa and bank accounts.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('payments.index') }}" class="btn btn-light mr-2">Payments</a>
            <a href="{{ route('payment-accounts.create') }}" class="btn btn-primary">
                <x-heroicon-o-plus class="w-5 h-5 mr-1 inline" /> Add Account
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
    @endif

    <h5 class="payment-account-section-title mb-2">Account Type Stats</h5>
    <div class="payment-type-stats-grid">
        @foreach (['bank' => 'Bank', 'jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa'] as $type => $label)
        @php
        $stats = $accountTypeStats[$type];
        @endphp
        <div class="card payment-type-stats-card payment-account-summary-card-{{ $type }} mb-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>{{ $label }}</strong>
                    <small>{{ $stats['accounts_count'] }} {{ \Illuminate\Support\Str::plural('account', $stats['accounts_count']) }}</small>
                </div>
                <div class="payment-type-stats-metrics">
                    <span>Opening <strong>Rs {{ number_format($stats['opening_balance'], 0) }}</strong></span>
                    <span>Transactions <strong>{{ number_format($stats['transactions_count']) }}</strong></span>
                    <span>Transactions Amount <strong>Rs {{ number_format($stats['total_transaction_amount'], 0) }}</strong></span>
                    <span>Current Balance <strong>Rs {{ number_format($stats['current_balance'], 0) }}</strong></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <style>
        .payment-type-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 1rem;
        }

        .payment-type-stats-card {
            color: #fff;
            border: 0;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(20, 35, 55, 0.2);
            overflow: hidden;
        }

        .payment-type-stats-card .card-body {
            padding: 10px 12px;
        }

        .payment-type-stats-card .card-body>.d-flex {
            margin-bottom: 3px !important;
        }

        .payment-type-stats-card .card-body>.d-flex strong {
            font-size: 13px;
            font-weight: 600;
        }

        .payment-type-stats-card small {
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }

        .payment-type-stats-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 3px 8px;
            font-size: 13px;
        }

        .payment-type-stats-metrics span {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            color: rgba(255, 255, 255, 0.82);
        }

        .payment-type-stats-metrics strong {
            color: #fff;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 700;
        }

        .payment-account-summary-card-bank {
            color: #fff;
            background-image: linear-gradient(120deg, #0b3768 0%, #1769aa 48%, #398bd0 100%);
            border-top: 2px solid #0b3a6b;
        }

        .payment-account-summary-card-easypaisa {
            color: #fff;
            background-image: linear-gradient(120deg, #064326 0%, #0c8045 48%, #25a765 100%);
            border-top: 2px solid #064628;
        }

        .payment-account-summary-card-jazzcash {
            color: #fff;
            background-image: linear-gradient(120deg, #741722 0%, #b62435 48%, #e04b57 100%);
            border-top: 2px solid #741722;
        }

        .payment-account-summary-card:not(.payment-account-summary-card-bank):not(.payment-account-summary-card-easypaisa):not(.payment-account-summary-card-jazzcash) {
            color: #fff;
            background-image: linear-gradient(120deg, #352258 0%, #69449e 48%, #9470c8 100%);
            border-top: 2px solid #352258;
        }

        @media (max-width: 575.98px) {
            .payment-type-stats-grid {
                gap: 8px;
            }

            .payment-type-stats-card .card-body {
                padding: 5px;
            }

            .payment-type-stats-metrics {
                grid-template-columns: 1fr;
                gap: 2px;
                font-size: 13px;
            }

            .payment-type-stats-metrics strong {
                font-size: 13px;
            }
        }
    </style>

    <div class="card card-block card-stretch card-height">
        <div class="card-body table-responsive">
            <table class="table payment-account-table mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Account Number</th>
                        <th>Opening Balance</th>
                        <th>Current Balance</th>
                        <th>Transactions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $accountRow)
                    <tr>
                        <td>{{ $accountRow->name }}</td>
                        <td>{{ ucfirst($accountRow->type) }}</td>
                        <td>{{ $accountRow->account_number ?: '-' }}</td>
                        <td>Rs {{ number_format($accountRow->opening_balance, 2) }}</td>
                        <td>Rs {{ number_format($accountRow->balance, 2) }}</td>
                        <td>{{ $accountRow->transactions_count }}</td>
                        <td>
                            <span class="badge {{ $accountRow->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($accountRow->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('payment-accounts.edit', $accountRow) }}" class="btn btn-light btn-sm" title="Edit account">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </a>
                            <form action="{{ route('payment-accounts.destroy', $accountRow) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete or deactivate this account?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light btn-sm" title="Delete or deactivate account">
                                    <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No payment accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $accounts->links() }}</div>
        </div>
    </div>
</div>
@endsection