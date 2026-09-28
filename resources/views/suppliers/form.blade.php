@php($inModal = $inModal ?? false)

<form @if ($inModal) id="supplier-form" @endif action="{{ route('suppliers.store') }}" method="POST">
    @csrf

    @if ($inModal)
        <div id="supplier-errors" class="alert alert-danger py-2" style="display:none;font-size:.78rem;"></div>
    @endif

    <div class="row align-items-center">
        <div class="form-group col-md-6">
            <label for="name">Supplier Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group col-md-6">
            <label for="email">Supplier Email <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group col-md-6">
            <label for="phone">Supplier Phone <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                name="phone" value="{{ old('phone') }}" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group col-md-6">
            <label for="city">Supplier City <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                name="city" value="{{ old('city') }}" required>
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group col-md-12">
            <label for="address">Supplier Address <span class="text-danger">*</span></label>
            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" required>{{ old('address') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Section: Form Actions --}}
    <div class="mt-2">
        <button type="submit" @if ($inModal) id="save-supplier" @endif class="btn btn-primary mr-2">
            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
        </button>

        @if ($inModal)
            <button type="button" id="cancel-supplier" class="btn btn-orange">
                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
            </button>
        @else
            <a class="btn btn-orange" href="{{ route('suppliers.index') }}">
                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
            </a>
        @endif
    </div>
</form>