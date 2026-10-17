@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Alert: Success Message -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                <!-- Header: Page Title and Add Button -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Employee List</h4>
                        <p class="mb-0">Manage all your employees from one place. <br> View, create, update, or delete
                            employee records.</p>
                    </div>
                    <div>
                        <a href="{{ route('employees.create') }}" class="btn btn-primary add-list">
                            <x-heroicon-o-plus class="w-5 h-5 mr-3" />Add Employee
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('employees.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request('search'))
                        <a href="{{ route('employees.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </a>
                        @endif
                    </form>
                    @if (isset($employees) && method_exists($employees, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $employees->total() }} records</span>
                    @endif
                </div>
            </div>

            <!-- Table: Employee Records -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th>Photo</th>
                                <th><x-sort-link name="name" label="Name" /></th>
                                <th><x-sort-link name="email" label="Email" /></th>
                                <th><x-sort-link name="phone" label="Phone" /></th>
                                <th><x-sort-link name="salary" label="Salary" /></th>
                                <th><x-sort-link name="city" label="City" /></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ (($employees->currentPage() * $employees->perPage()) - $employees->perPage()) + $loop->iteration }}
                                    </td>
                                    <td>
                                        <img class="avatar-60 rounded"
                                            src="{{ $employee->photo ? asset('storage/employees/' . $employee->photo) : asset('assets/images/user/1.png') }}"
                                            style="object-fit: cover;">
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->phone }}</td>
                                    <td>${{ number_format($employee->salary, 2) }}</td>
                                    <td>{{ $employee->city }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center list-action">
                                            <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top"
                                                title="View" href="{{ route('employees.show', $employee->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            <a class="btn btn-primary mr-2" data-toggle="tooltip" data-placement="top"
                                                title="Edit" href="{{ route('employees.edit', $employee->id) }}">
                                                <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                            </a>
                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
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
                                    <td colspan="8" class="text-center">
                                        <div class="alert text-white bg-warning mt-2" role="alert">
                                            <div class="iq-alert-text">No records found.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                {{ $employees->links() }}
            </div>
        </div>
    </div>
@endsection
