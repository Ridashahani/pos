@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
            <div class="alert text-white bg-success" role="alert">
                <div class="iq-alert-text">{{ session('success') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            @endif
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">User List</h4>
                </div>
                <div>
                    @can('create.users')
                        <a href="{{ route('users.create') }}" class="btn btn-primary add-list">
                            <x-heroicon-o-plus class="w-5 h-5 mr-3" />Create User
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                <form action="{{ route('users.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                    <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                        <input type="search" name="search" class="form-control" placeholder="Search by name, username, email..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </button>
                    @if (request('search'))
                    <a href="{{ route('users.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </a>
                    @endif
                </form>
                @if (isset($users) && method_exists($users, 'total'))
                <span class="badge badge-light border px-3 py-2">{{ $users->total() }} records</span>
                @endif
            </div>
        </div>

        <div class="col-lg-12">
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>No.</th>
                            <th><x-sort-link name="name" label="Name" /></th>
                            <th><x-sort-link name="username" label="Username" /></th>
                            <th><x-sort-link name="email" label="Email" /></th>
                            <th>Role</th>
                            <th>Branches</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @forelse ($users as $item)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->username }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                @foreach ($item->roles as $role)
                                <span class="badge bg-danger">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @forelse ($item->branches as $branch)
                                <span class="badge text-dark mr-1"
                                    style="font-size: inherit; font-weight: normal;">{{ $branch->name }}</span>
                                @empty
                                <span class="text-muted">
                                    {{ $item->roles->contains(fn($r) => strtolower($r->name) === 'admin') ? 'All Branches' : '-' }}
                                </span>
                                @endforelse
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center list-action">
                                    @can('edit.users')
                                        <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                            title="Edit" href="{{ route('users.edit', $item->username) }}">
                                            <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                        </a>
                                    @endcan
                                    @can('delete.users')
                                        <form action="{{ route('users.destroy', $item->username) }}" method="POST"
                                            style="display:inline;">
                                            @method('delete')
                                            @csrf
                                            <button type="submit" class="btn btn-danger border-0"
                                                onclick="return confirm('Are you sure you want to delete this record?')"
                                                data-toggle="tooltip" data-placement="top" title="Delete">
                                                <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert text-white bg-danger" role="alert">
                                    <div class="iq-alert-text">Data not Found.</div>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
    <!-- Page end  -->
</div>
@endsection