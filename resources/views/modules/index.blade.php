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

    .module-status {
        background: #e7f7ef;
        border-radius: 14px;
        color: #16804b;
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-2">{{ $title }}</h4>
                    <p class="mb-0 text-muted">{{ $description }}</p>
                </div>
                @if (($module ?? null) === 'branches')
                <a href="{{ route('branches.create') }}" class="btn btn-primary add-list d-flex align-items-center mt-3 mt-md-0">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Branch
                </a>

                @elseif (($module ?? null) === 'expenses')
                <a href="{{ route('expenses.create') }}" class="btn btn-primary add-list d-flex align-items-center mt-3 mt-md-0">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Expense
                </a>

                @endif
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
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search {{ strtolower($title) }}..." value="{{ request('search') }}">
                        </div>
                        <span class="badge badge-light border px-3 py-2">{{ count($records ?? []) }} records</span>
                    </div>

                    @if (($module ?? null) === 'branches')
                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Branch</th>
                                    <th>Code</th>
                                    <th>Address</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                <tr>
                                    <td><strong>{{ $record['name'] }}</strong></td>
                                    <td>{{ $record['code'] }}</td>
                                    <td>{{ $record['address'] }}</td>
                                    <td>{{ $record['phone'] }}</td>
                                    <td><span class="module-status">{{ $record['status'] }}</span></td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-light btn-sm mr-1" title="Edit branch">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" title="Delete branch">
                                            <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No branches found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @elseif (($module ?? null) === 'expenses')
                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Branch</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                <tr>
                                    <td>{{ $record['date'] }}</td>
                                    <td>{{ $record['branch'] }}</td>
                                    <td><span class="badge badge-light">{{ $record['category'] }}</span></td>
                                    <td><strong>{{ $record['amount'] }}</strong></td>
                                    <td>{{ $record['note'] }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-light btn-sm mr-1" title="Edit expense">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" title="Delete expense">
                                            <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                        </button>
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

                    @else
                    <p class="text-muted mb-0">{{ $description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection