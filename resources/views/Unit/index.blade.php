@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
            <div class="alert text-white bg-success" role="alert">
                <div class="iq-alert-text">{{ session('success') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Unit List</h4>
                </div>
                <a href="{{ route('units.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Unit
                </a>
            </div>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('units.index') }}" method="get" class="mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">

                    <div class="form-group mb-2">
                        <div class="input-group ">
                            <input type="text" class="form-control" name="search" placeholder="Search units"
                                value="{{ request('search') }}">
                            <div class="input-group-append">
                                @if (request()->filled('search'))
                                <a href="{{ route('units.index') }}" class="input-group-text bg-secondary text-white mr-2 ml-2"
                                    aria-label="Clear search" title="Clear">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </a>
                                @endif

                                <button type="submit" class="input-group-text bg-primary" aria-label="Search">
                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                </button>
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
                            <th><x-sort-link name="short_name" label="Short Name" /></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @forelse ($units as $unit)
                        <tr>
                            <td>{{ (($units->currentPage() - 1) * $units->perPage()) + $loop->iteration }}</td>
                            <td>{{ $unit->name }}</td>
                            <td>{{ $unit->short_name }}</td>
                            <td>
                                <div class="d-flex align-items-center list-action">
                                    <a class="btn btn-success mr-2" href="{{ route('units.edit', $unit) }}"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                    </a>
                                    <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-danger border-0"
                                            onclick="return confirm('Are you sure you want to delete this unit?')"
                                            data-toggle="tooltip" data-placement="top" title="Delete">
                                            <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No units found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="justify-content-start">
                {{ $units->links() }}
            </div>
        </div>
    </div>
</div>
@endsection