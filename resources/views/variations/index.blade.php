@extends('dashboard.body.main')

@section('container')
<div class="container-fluid variation-page">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <form action="{{ route('variations.index') }}" method="get" class="w-50"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search"></form><a href="{{ route('variations.create') }}" class="btn btn-primary">Create Variation</a>
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