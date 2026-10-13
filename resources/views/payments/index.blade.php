@extends('dashboard.body.main')

@section('container')
<style>
    .payment-page-title {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.2;
    }

    .module-toolbar .form-control,
    .module-toolbar .btn {
        height: 42px;
    }

    .payments-page-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .payments-page-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 50px;
        margin: 0 !important;
        padding: 0 16px;
        line-height: 1;
    }

    .payment-summary-card {
        min-height: 0;
        border-radius: 5px;
        border: 1px solid transparent;
        box-shadow: 0 2px 7px rgba(34, 60, 80, 0.08);
    }

    .payment-summary-card .card-body {
        padding: 24px 14px;
    }

    .payment-summary-transactions {
        background: linear-gradient(135deg, #eaf3ff, #fff);
        border-color: #c7ddf7;
        border-top: 3px solid #3788d8;
    }

    .payment-summary-sent {
        background: linear-gradient(135deg, #e7f7ef, #fff);
        border-color: #c6e9d5;
        border-top: 3px solid #28a76b;
    }

    .payment-summary-withdrawals {
        background: linear-gradient(135deg, #fff2e4, #fff);
        border-color: #f4dcc2;
        border-top: 3px solid #e99238;
    }

    .payment-summary-profit {
        background: linear-gradient(135deg, #f1ebff, #fff);
        border-color: #ded1f7;
        border-top: 3px solid #8056c7;
    }

    .payment-summary-card p {
        font-size: 13px !important;
        font-weight: 600;
        line-height: 1.2;
    }

    .payment-summary-card h5 {
        font-size: 19px;
        font-weight: 700;
        line-height: 1.2;
    }

    .module-table thead.bg-primary th {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        padding: 12px 10px;
        vertical-align: middle;
    }

    .module-table {
        min-width: 1200px;
    }

    .module-table td {
        color: #1f2937;
        vertical-align: middle;
    }

    .module-table th:first-child,
    .module-table td:first-child,
    .module-table th:nth-child(5),
    .module-table td:nth-child(5),
    .module-table th:nth-child(6),
    .module-table td:nth-child(6),
    .module-table th:nth-child(7),
    .module-table td:nth-child(7),
    .module-table th:nth-child(8),
    .module-table td:nth-child(8),
    .module-table th:last-child,
    .module-table td:last-child {
        white-space: nowrap;
    }

    .payment-row-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .payment-row-actions form {
        margin: 0;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="payment-page-title mb-2">Payments</h4>
                    <p class="mb-0 text-muted">Money transfer &amp; cash withdrawal transactions.</p>
                </div>
                <div class="payments-page-actions mt-3 mt-md-0">
                    <a href="{{ route('payment-accounts.index') }}" class="btn btn-light">Manage Accounts</a>
                    <a href="#fund-transfer" class="btn btn-success">Fund Transfer</a>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary add-list">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Transaction
                    </a>
                </div>
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
            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Summary stats --}}
        <div class="col-lg-12">
            <div class="row">
                <div class="col-6 col-md-3 px-2">
                    <div class="card card-block payment-summary-card payment-summary-transactions mb-3">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Transactions</p>
                            <h5 class="mb-0">{{ $summary['total_transactions'] }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-2">
                    <div class="card card-block payment-summary-card payment-summary-sent mb-3">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Sent</p>
                            <h5 class="mb-0">Rs {{ number_format($summary['total_sent'], 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-2">
                    <div class="card card-block payment-summary-card payment-summary-withdrawals mb-3">
                        <div class="card-body">
                            <p class="text-muted mb-1" style="font-size:12px;">Total Withdrawals</p>
                            <h5 class="mb-0">Rs {{ number_format($summary['total_withdrawals'], 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-2">
                    <div class="card card-block payment-summary-card payment-summary-profit mb-3">
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
                                    <th>Transaction IDs</th>
                                    <th>Customer Name Phone No</th>
                                    <th>Recipient Name Phone No</th>
                                    <th>Transaction Type</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Commission</th>
                                    <th>Date &amp; Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $tx)
                                <tr>
                                    <td>{{ $tx->transaction_id }}</td>
                                    <td>
                                        <div>{{ $tx->customer_name ?: '—' }}</div>
                                        @if ($tx->customer_phone)<small class="text-muted">{{ $tx->customer_phone }}</small>@endif
                                    </td>
                                    <td>
                                        <div>{{ $tx->recipient_name ?: '—' }}</div>
                                        @if ($tx->recipient_phone)<small class="text-muted">{{ $tx->recipient_phone }}</small>@endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $tx->type === 'send' ? 'badge-primary' : 'badge-success' }}">
                                            {{ $tx->type === 'send' ? 'Send Money' : 'Cash Withdrawal' }}
                                        </span>
                                    </td>
                                    <td>{{ $tx->account->name ?? '—' }}</td>
                                    <td class="text-nowrap"><strong>{{ number_format($tx->amount, 2) }}</strong></td>
                                    <td class="text-nowrap">{{ number_format($tx->commission, 2) }}</td>
                                    <td>{{ $tx->transaction_date->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <div class="payment-row-actions">
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
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header">
                    <h5 class="mb-0">Transfer Funds Between Accounts</h5>
                </div>
                <div class="card-body">
                    @if ($accounts->count() < 2)
                    <div class="alert alert-warning mb-0">
                        At least two active payment accounts are required to transfer funds.
                    </div>
                    @else
                    <form action="{{ route('payment-transfers.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="from_payment_account_id">From Account <span class="text-danger">*</span></label>
                                <select id="from_payment_account_id" name="from_payment_account_id" class="form-control @error('from_payment_account_id') is-invalid @enderror" required>
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('from_payment_account_id') == $account->id)>
                                        {{ $account->name }} (Rs {{ number_format($account->available_balance, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('from_payment_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="to_payment_account_id">To Account <span class="text-danger">*</span></label>
                                <select id="to_payment_account_id" name="to_payment_account_id" class="form-control @error('to_payment_account_id') is-invalid @enderror" required>
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('to_payment_account_id') == $account->id)>
                                        {{ $account->name }} (Rs {{ number_format($account->available_balance, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('to_payment_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-2">
                                <label for="transfer_amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" id="transfer_amount" name="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-2">
                                <label for="transfer_note">Note</label>
                                <input id="transfer_note" name="note" value="{{ old('note') }}" class="form-control @error('note') is-invalid @enderror" maxlength="255">
                                @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Transfer Funds</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header">
                    <h5 class="mb-0">Recent Fund Transfers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>From Account</th>
                                    <th>To Account</th>
                                    <th>Amount</th>
                                    <th>Date &amp; Time</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->fromAccount->name ?? '—' }}</td>
                                    <td>{{ $transfer->toAccount->name ?? '—' }}</td>
                                    <td class="text-nowrap"><strong>Rs {{ number_format($transfer->amount, 2) }}</strong></td>
                                    <td>{{ $transfer->transfer_date->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                                    <td>{{ $transfer->note ?: '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No fund transfers found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fromAccount = document.getElementById('from_payment_account_id');
        const toAccount = document.getElementById('to_payment_account_id');

        if (!fromAccount || !toAccount) {
            return;
        }

        function updateDestinationAccounts() {
            Array.from(toAccount.options).forEach(function (option) {
                option.disabled = option.value !== '' && option.value === fromAccount.value;
            });

            if (toAccount.value === fromAccount.value) {
                toAccount.value = '';
            }
        }

        fromAccount.addEventListener('change', updateDestinationAccounts);
        updateDestinationAccounts();
    });
</script>
@endsection