@php($editing = isset($subcategory) && $subcategory)
<form action="{{ $editing ? route('subcategories.update', $subcategory->slug) : route('subcategories.store') }}" method="POST">
    @csrf
    @if ($editing) @method('put') @endif
    <div class="form-group">
        <label for="category_id">Parent Category <span class="text-danger">*</span></label>
        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
            <option value="">Choose Parent Category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $editing ? $subcategory->category_id : '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="name">Subcategory Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $editing ? $subcategory->name : '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="slug">Subcategory Slug <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $editing ? $subcategory->slug : '') }}" required readonly>
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $editing ? $subcategory->description : '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-save mr-2"><x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> {{ $editing ? 'Update' : 'Save' }}</button>
    <a class="btn btn-cancel" href="{{ route('subcategories.index') }}"><x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel</a>
</form>
<script>
    document.querySelector('#name').addEventListener('keyup', function () {
        document.querySelector('#slug').value = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9 ]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    });
</script>
