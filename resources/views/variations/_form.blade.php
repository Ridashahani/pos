@php $editing = isset($variation) && $variation; $types = old('types', $editing ? $variation->types : ['']); @endphp
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">{{ $editing ? 'Edit Variation' : 'Create Variation' }}</h4>
            <form action="{{ $formAction }}" method="post">@csrf @if($formMethod !== 'POST') @method($formMethod) @endif
                <div class="form-group"><label>Variation Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $editing ? $variation->name : '') }}" class="form-control" placeholder="e.g. Size" required>@error('name')<div class="text-danger">{{ $message }}</div>@enderror</div>
                <label>Variation Types <span class="text-danger">*</span></label>
                <div id="variation-types">@foreach($types as $type)<div class="input-group mb-2 type-row"><input name="types[]" value="{{ $type }}" class="form-control" placeholder="e.g. Small, Medium, Large" required>
                        <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-type">Remove</button></div>
                    </div>@endforeach</div>
                @error('types')<div class="text-danger">{{ $message }}</div>@enderror
                <button type="button" id="add-type" class="btn btn-outline-primary mb-4">Add Type</button>
                <div class="d-flex justify-content-start"><button class="btn btn-save mr-2"><x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> {{ $submitLabel }}</button><a href="{{ route('variations.index') }}" class="btn btn-cancel"><x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel</a></div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('add-type').addEventListener('click', function() {
        const row = document.querySelector('.type-row').cloneNode(true);
        row.querySelector('input').value = '';
        document.getElementById('variation-types').appendChild(row);
    });
    document.getElementById('variation-types').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-type') && document.querySelectorAll('.type-row').length > 1) event.target.closest('.type-row').remove();
    });
</script>