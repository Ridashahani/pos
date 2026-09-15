@extends('dashboard.body.main')

@section('container')
<style>
    .btn-pure-red {
        background-color: #ff0000 !important;
        border-color: #ff0000 !important;
        color: #fff !important;
    }

    .btn-pure-red:hover,
    .btn-pure-red:focus {
        background-color: #d90000 !important;
        border-color: #d90000 !important;
        color: #fff !important;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Create Branch</h4>
                    <a href="{{ route('branches.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>
                <div class="card-body">
                    <form onsubmit="return false;">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="name">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}"
                                    class="form-control @error('address') is-invalid @enderror" required>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                    class="form-control @error('phone') is-invalid @enderror">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="Active" @selected(old('status', 'Active' )==='Active' )>Active</option>
                                    <option value="Inactive" @selected(old('status')==='Inactive' )>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary mr-2">Save Branch</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-pure-red">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection