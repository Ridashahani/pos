@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Edit User</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('users.update', $userData->username) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                            <!-- begin: Input Image -->


                            <!-- end: Input Image -->
                            <!-- begin: Input Data -->
                            <div class=" row align-items-center">
                                <div class="form-group col-md-12">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $userData->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $userData->username) }}" required>
                                    @error('username')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $userData->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="role">Role</label>
                                <select class="form-control @error('role') is-invalid @enderror" name="role">
                                    <option selected="" disabled> Select Role </option>
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ $userData->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <!-- end: Input Data -->
                        <div class="mt-2">
                            <button type="submit" class="btn btn-save mr-2">
                                <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
                            </button>
                            <a class="btn btn-cancel" href="{{ route('users.index') }}">
                                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Page end  -->
</div>

@include('components.preview-img-form')
@endsection