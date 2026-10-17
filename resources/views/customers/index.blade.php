@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Alert: Session Status -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                <!-- Section: Header and Add Button -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Customer List</h4>
                        <p class="mb-0">A customer dashboard lets you easily gather and visualize customer data from
                            optimizing <br>
                            the customer experience, ensuring customer retention. </p>
                    </div>
                    <div>
                        @can('create.customers')
                            <a href="{{ route('customers.create') }}" class="btn btn-primary add-list">
                                <x-heroicon-o-plus class="w-5 h-5 mr-3" />Add Customer
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Section: Filters and Search -->
            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('customers.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search by name, city..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request('search'))
                        <a href="{{ route('customers.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </a>
                        @endif
                    </form>
                    @if (isset($customers) && method_exists($customers, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $customers->total() }} records</span>
                    @endif
                </div>
            </div>

            <!-- Section: Customer Table -->
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
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>{{ $customers->currentPage() * 10 - 10 + $loop->iteration }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->city }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center list-action">
                                            <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top"
                                                title="View" href="{{ route('customers.show', $customer->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            @can('edit.customers')
                                                <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                                    title="Edit" href="{{ route('customers.edit', $customer->id) }}">
                                                    <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                </a>
                                            @endcan
                                            @can('delete.customers')
                                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
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

                <!-- Section: Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
