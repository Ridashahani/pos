@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-2">Payment Accounts</h4>
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

    <div class="card card-block card-stretch card-height">
        <div class="card-body table-responsive">
            <table class="table mb-0">
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
                    @forelse ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td>{{ ucfirst($account->type) }}</td>
                        <td>{{ $account->account_number ?: '-' }}</td>
                        <td>Rs {{ number_format($account->opening_balance, 2) }}</td>
                        <td>Rs {{ number_format($account->balance, 2) }}</td>
                        <td>{{ $account->transactions_count }}</td>
                        <td>
                            <span class="badge {{ $account->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($account->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('payment-accounts.edit', $account) }}" class="btn btn-light btn-sm" title="Edit account">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </a>
                            <form action="{{ route('payment-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete or deactivate this account?');">
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