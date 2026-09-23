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
    
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-2">Expenses</h4>
                    <p class="mb-0 text-muted">Operating expenses recorded per branch.</p>
                </div>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary add-list d-flex align-items-center mt-3 mt-md-0">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Expense
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

            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                       <form action="{{ route('expenses.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                            <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                <input type="search" name="search" class="form-control" placeholder="Search....." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </button>
                            @if (request('search'))
                            <a href="{{ route('expenses.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </a>
                            @endif
                        </form>
                        <span class="badge badge-light border px-3 py-2">{{ $expenses->total() }} records</span>
                    </div>

                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Branch</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date }}</td>
                                    <td>{{ $expense->branch->name }}</td>
                                    <td><span class="badge badge-light">{{ $expense->category }}</span></td>
                                    <td><strong>{{ number_format($expense->amount, 2) }}</strong></td>
                                    <td>{{ $expense->description }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-light btn-sm mr-1" title="Edit expense">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm" title="Delete expense">
                                                <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No expenses found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection