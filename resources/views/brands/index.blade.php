@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div id="brand-success-alert" class="alert alert-dismissible fade show text-white bg-success" role="status">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <h4 class="mb-0">Brands List</h4>
                    <div class="d-flex flex-nowrap align-items-center" style="width: 410px; max-width: 100%;">
                        <form id="brand-search-form" action="{{ route('brands.index') }}" method="GET"
                            class="mr-2" style="min-width: 0; flex: 1 1 240px;">
                            <input id="brand-search" name="search" type="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Search brands..."
                                aria-label="Search brands by name">
                        </form>
                        <a href="{{ route('brands.create') }}" class="btn btn-primary add-list d-flex align-items-center"
                            style="white-space: nowrap; flex-shrink: 0;">
                            <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add New Brand
                        </a>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($brands as $brand)
                                <tr>
                                    <td>{{ $brand->name }}</td>
                                    <td>
                                        <div class="d-flex align-items-center list-action">
                                            <a class="btn btn-success mr-2" href="{{ route('brands.edit', $brand) }}"
                                                title="Edit" aria-label="Edit {{ $brand->name }}">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <form action="{{ route('brands.destroy', $brand) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger border-0" title="Delete"
                                                    aria-label="Delete {{ $brand->name }}"
                                                    onclick="return confirm('Are you sure you want to delete this brand?')">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        @if (request('search') !== null && request('search') !== '')
                                            No brands match your search.
                                        @else
                                            No brands found. Click "Add New Brand" to add one.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($brands->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $brands->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if (session('success'))
        <script>
            window.setTimeout(() => {
                const alert = document.getElementById('brand-success-alert');
                if (alert) {
                    alert.classList.remove('show');
                    window.setTimeout(() => alert.remove(), 150);
                }
            }, 2000);
        </script>
    @endif
    <script>
        const brandSearch = document.getElementById('brand-search');
        let searchTimeout;

        brandSearch.addEventListener('input', () => {
            window.clearTimeout(searchTimeout);
            searchTimeout = window.setTimeout(() => {
                brandSearch.form.submit();
            }, 300);
        });
    </script>
@endsection