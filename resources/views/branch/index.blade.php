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

    .module-status.inactive {
        background: #fdeceb;
        color: #c0392b;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-2">Branches</h4>
                    <p class="mb-0 text-muted">Store branches and locations.</p>
                </div>
                <a href="{{ route('branches.create') }}" class="btn btn-primary add-list d-flex align-items-center mt-3 mt-md-0">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Branch
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
                        <form action="{{ route('branches.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                            <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                                <input type="search" name="search" class="form-control" placeholder="Search....." value="{{ request('search') }}">

                            </div>
                            <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </button>
                            @if (request('search'))
                            <a href="{{ route('branches.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </a>
                            @endif

                        </form>
                        <span class="badge badge-light border px-3 py-2">{{ $branches->total() }} records</span>
                    </div>

                    <div class="table-responsive rounded">
                        <table class="table module-table mb-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Branch</th>
                                    <!-- <th>Code</th> -->
                                    <th>Address</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                <tr>
                                    <td><strong>{{ $branch->name }}</strong></td>
                                    <td>{{ $branch->address }}</td>
                                    <td>{{ $branch->phone }}</td>
                                    <td>
                                        <span class="module-status {{ $branch->status === 'Inactive' ? 'inactive' : '' }}">
                                            {{ $branch->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('branches.edit', $branch) }}" class="btn btn-light btn-sm mr-1" title="Edit branch">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </a>
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm" title="Delete branch">
                                                <x-heroicon-o-trash class="w-4 h-4 text-danger" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No branches found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $branches->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection