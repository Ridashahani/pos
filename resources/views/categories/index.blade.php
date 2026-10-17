@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0; /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        @media (min-width: 576px) {
            .row-selector-container {
                min-width: 180px; /* Apply min-width only on sm+ screens */
                padding-top: 0;
                padding-bottom: 0;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Section: Session Alerts -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert text-white bg-danger" role="alert">
                        <div class="iq-alert-text">{{ session('error') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <!-- Section: Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Category List</h4>
                        <p class="mb-0">
                            Manage your product categories effectively to improve organization <br>
                            and customer navigation.
                        </p>
                    </div>
                    <div class="d-flex align-items-center">
                        @can('create.categories')
                            <a href="{{ route('categories.create') }}" class="btn btn-primary add-list d-flex align-items-center mr-3">
                                <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Create Category
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Section: Filters -->
            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('categories.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search by category name..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request('search'))
                        <a href="{{ route('categories.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </a>
                        @endif
                    </form>
                    @if (isset($categories) && method_exists($categories, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $categories->total() }} records</span>
                    @endif
                </div>
            </div>

            <!-- Section: Table -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th><x-sort-link name="name" label="Name" /></th>
                                <th><x-sort-link name="slug" label="Slug" /></th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ (($categories->currentPage() * 10) - 10) + $loop->iteration }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->slug }}</td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center list-action">
                                            <!-- Edit Button -->
                                            @can('edit.categories')
                                                <a class="btn btn-success mr-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"
                                                    href="{{ route('categories.edit', $category->slug) }}">
                                                    <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                </a>
                                            @endcan

                                            <!-- Delete Button -->
                                            @can('delete.categories')
                                                <form action="{{ route('categories.destroy', $category->slug) }}" method="POST"
                                                    class="d-inline">
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
                                    <td colspan="5" class="text-center">
                                        <span class="text-muted">No categories found. Click "Create Category" to add one.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
