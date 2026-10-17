@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                {{-- Alert: Session Status --}}
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                {{-- Section: Header and Add Button --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Supplier List</h4>
                        <p class="mb-0">A supplier dashboard lets you easily gather and visualize supplier data from
                            optimizing <br>
                            the supplier experience, ensuring supplier retention. </p>
                    </div>
                    <div>
                        @can('create.suppliers')
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary add-list">
                                <x-heroicon-o-plus class="w-5 h-5 mr-3" />Add Supplier
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Section: Filters and Search --}}
            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('suppliers.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search by name, email, city..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request('search'))
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </a>
                        @endif
                    </form>
                    @if (isset($suppliers) && method_exists($suppliers, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $suppliers->total() }} records</span>
                    @endif
                </div>
            </div>

            {{-- Section: Supplier Table --}}
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th><x-sort-link name="name" label="Name" /></th>
                                <th><x-sort-link name="email" label="Email" /></th>
                                <th><x-sort-link name="phone" label="Phone" /></th>
                                <th><x-sort-link name="city" label="City" /></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @foreach ($suppliers as $supplier)
                                <tr>
                                    <td>{{ $suppliers->currentPage() * 10 - 10 + $loop->iteration }}</td>
                                    <td>{{ $supplier->name }}</td>
                                    <td>{{ $supplier->email }}</td>
                                    <td>{{ $supplier->phone }}</td>
                                    <td>{{ $supplier->city }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center list-action">
                                            <a class="btn btn-primary mr-2" data-toggle="tooltip" data-placement="top"
                                                title="View" href="{{ route('suppliers.show', $supplier->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            @can('edit.suppliers')
                                                <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                                    title="Edit" href="{{ route('suppliers.edit', $supplier->id) }}">
                                                    <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                </a>
                                            @endcan
                                            @can('delete.suppliers')
                                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST"
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
@endsection
