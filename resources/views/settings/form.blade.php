@php($setting = $setting ?? null)
<div class="row align-items-center">
    <div class="form-group col-md-6">
        <label for="key">Key <span class="">*</span></label>
        <input type="text" id="key" name="key" value="{{ old('key', $setting->key ?? '') }}" class="form-control @error('key') is-invalid @enderror" required>
        @error('key')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="value">Value <span class="">*</span></label>
        <input type="text" id="value" name="value" value="{{ old('value', $setting->value ?? '') }}" class="form-control @error('value') is-invalid @enderror" required>
        @error('value')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>