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
                <a href="{{ route('subcategories.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Create Subcategory
                </a>
            </div>
        </div>
        <div class="col-lg-12">
            <form action="{{ route('subcategories.index') }}" method="get" class="d-flex justify-content-between mb-3">
                <select class="form-control" name="row" style="max-width: 100px;">
                    @foreach ([10, 25, 50, 100] as $pageSize)
                        <option value="{{ $pageSize }}" @selected(request('row', 10) == $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
                <div class="input-group ml-3" style="max-width: 360px;">
                    <input type="text" class="form-control" name="search" placeholder="Search subcategory" value="{{ request('search') }}">
                    <div class="input-group-append"><button type="submit" class="input-group-text bg-primary"><x-heroicon-o-magnifying-glass class="w-5 h-5" /></button></div>
                </div>
            </form>
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
                                    <a class="btn btn-success mr-2" href="{{ route('subcategories.edit', $subcategory->slug) }}" title="Edit"><x-heroicon-o-pencil class="w-5 h-5" /></a>
                                    <form action="{{ route('subcategories.destroy', $subcategory->slug) }}" method="POST" class="d-inline">@csrf @method('delete')
                                        <button type="submit" class="btn btn-danger border-0" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete"><x-heroicon-o-trash class="w-5 h-5" /></button>
                                    </form>
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
