@extends('dashboard.body.main')

@section('specificpagestyles')
    <style>
        .role-input {
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            padding: 10px 14px;
            max-width: 420px;
        }

        .role-input:focus {
            outline: none;
            border-color: #2f6fed;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, .12);
        }
    </style>
@endsection

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Create Role</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('role.store') }}" method="POST">
                            @csrf
                            <!-- begin: Input Data -->
                            <div class="row align-items-center">
                                <div class="form-group col-md-6">
                                    <label for="name">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control role-input @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="Enter Roll Name" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <!-- end: Input Data -->
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                                </button>
                                <a class="btn btn-orange" href="{{ route('role.index') }}">
                                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection