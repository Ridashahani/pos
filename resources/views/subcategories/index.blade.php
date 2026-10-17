@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session('success'))
                <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
            @endif
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Subcategory List</h4>
                    <p class="mb-0">Organize products beneath their parent categories.</p>
                </div>
                @can('create.categories')
                    <a href="{{ route('subcategories.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Create Subcategory
                    </a>
                @endcan
            </div>
        </div>
        <div class="col-lg-12">
            <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                <form action="{{ route('subcategories.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                    <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                        <input type="search" name="search" class="form-control" placeholder="Search by subcategory name..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </button>
                    @if (request('search'))
                    <a href="{{ route('subcategories.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </a>
                    @endif
                </form>
                @if (isset($subcategories) && method_exists($subcategories, 'total'))
                <span class="badge badge-light border px-3 py-2">{{ $subcategories->total() }} records</span>
                @endif
            </div>
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase"><tr><th>No.</th><th><x-sort-link name="name" label="Name" /></th><th>Category</th><th><x-sort-link name="slug" label="Slug" /></th><th>Description</th><th>Action</th></tr></thead>
                    <tbody class="ligth-body">
                        @forelse ($subcategories as $subcategory)
                            <tr>
                                <td>{{ (($subcategories->currentPage() - 1) * $subcategories->perPage()) + $loop->iteration }}</td>
                                <td>{{ $subcategory->name }}</td>
                                <td>{{ $subcategory->category->name }}</td>
                                <td>{{ $subcategory->slug }}</td>
                                <td>{{ Str::limit($subcategory->description, 50) }}</td>
                                <td><div class="d-flex align-items-center list-action">
                                    @can('edit.categories')
                                        <a class="btn btn-success mr-2" href="{{ route('subcategories.edit', $subcategory->slug) }}" title="Edit"><x-heroicon-o-pencil class="w-5 h-5" /></a>
                                    @endcan
                                    @can('delete.categories')
                                        <form action="{{ route('subcategories.destroy', $subcategory->slug) }}" method="POST" class="d-inline">@csrf @method('delete')
                                            <button type="submit" class="btn btn-danger border-0" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete"><x-heroicon-o-trash class="w-5 h-5" /></button>
                                        </form>
                                    @endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center"><span class="text-muted">No subcategories found. Click "Create Subcategory" to add one.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">{{ $subcategories->links() }}</div>
        </div>
    </div>
</div>
@endsection
