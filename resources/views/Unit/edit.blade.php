@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch card-height">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">Edit Unit</h4>
                        <a href="{{ route('units.index') }}" class="btn btn-light btn-sm">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('units.update', $unit) }}" method="POST">
                            @csrf
                            @method('put')
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input id="name" name="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $unit->name) }}" maxlength="255" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name <span class="text-danger">*</span></label>
                                <input id="short_name" name="short_name" type="text"
                                    class="form-control @error('short_name') is-invalid @enderror"
                                    value="{{ old('short_name', $unit->short_name) }}" maxlength="255" required>
                                @error('short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mr-2">
                                <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
                            </button>
                            <a class="btn btn-orange" href="{{ route('units.index') }}">
                                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection