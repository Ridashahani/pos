@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Edit Branch</h4>
                    <a href="{{ route('branches.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>
                <div class="card-body">
                    <form class="compact-form" action="{{ route('branches.update', $branch) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="name">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $branch->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="{{ old('address', $branch->address) }}" class="form-control @error('address') is-invalid @enderror">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="Active" @selected(old('status', $branch->status) === 'Active')>Active</option>
                                    <option value="Inactive" @selected(old('status', $branch->status) === 'Inactive')>Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">
                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
                        </button>
                        <a href="{{ route('branches.index') }}" class="btn btn-orange">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection