@extends('dashboard.body.main')

@section('container')
<div class="container-fluid variation-page">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <h4 class="mb-0">Variations</h4>
        <a href="{{ route('variations.create') }}" class="btn btn-primary d-flex align-items-center">
            <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Create Variation
        </a>
    </div>

    <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
        <form action="{{ route('variations.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
            <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                <input type="search" name="search" class="form-control" placeholder="Search variations..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
            </button>
            @if (request('search'))
            <a href="{{ route('variations.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </a>
            @endif
        </form>
        @if (isset($variations) && method_exists($variations, 'total'))
        <span class="badge badge-light border px-3 py-2">{{ $variations->total() }} records</span>
        @endif
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Variation Name</th>
                        <th>Variation Types</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variations as $variation)<tr>
                        <td>{{ $variation->name }}</td>
                        <td>{{ implode(', ', $variation->types) }}</td>
                        <td class="text-right">
                            <a href="{{ route('variations.edit', $variation) }}" class="btn btn-sm btn-success" title="Edit">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </a>
                            <form class="d-inline" action="{{ route('variations.destroy', $variation) }}" method="post">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this variation?')" title="Delete">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                    </tr>@empty<tr>
                        <td colspan="3" class="text-center text-muted">No variations found.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $variations->links() }}</div>
</div>
@endsection