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
                    <a href="{{ route('users.create') }}" class="btn btn-primary add-list">
                        <x-heroicon-o-plus class="w-5 h-5 mr-3" />Create User
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('users.index') }}" method="get">
                <div class="d-flex flex-wrap align-items-center justify-content-between">

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="input-group">
                                <input type="text" id="search" class="form-control" name="search"
                                    placeholder="Search user" value="{{ request('search') }}">
                                <div class="input-group-append ml-2">
                                    @if (request()->filled('search'))
                                    <a href="{{ route('users.index') }}" class="input-group-text bg-secondary text-white mr-2 ml-2"
                                        aria-label="Clear search" title="Clear">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </a>
                                    @endif
                                    <button type="submit" class="input-group-text bg-primary">
                                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
                                    <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                        title="Edit" href="{{ route('users.edit', $item->username) }}">
                                        <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                    </a>
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