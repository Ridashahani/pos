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
                        <form action="{{ route('users.update', $userData->username) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <!-- begin: Input Image -->


                            <!-- end: Input Image -->
                            <!-- begin: Input Data -->
                            <div class=" row align-items-center">
                                <div class="form-group col-md-12">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $userData->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                        id="username" name="username" value="{{ old('username', $userData->username) }}"
                                        required>
                                    @error('username')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $userData->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="role">Role</label>
                                    <select class="form-control @error('role') is-invalid @enderror" name="role"
                                        id="role">
                                        <option disabled> Select Role </option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" data-name="{{ strtolower($role->name) }}"
                                                {{ $userData->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6" id="branch-wrapper" style="display:none;">
                                    <label for="branch_ids">Branches <span class="text-danger">*</span></label>
                                    <select class="form-control @error('branch_ids') is-invalid @enderror"
                                        name="branch_ids[]" id="branch_ids" multiple size="4" style="height:auto;">
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ in_array($branch->id, old('branch_ids', $assigned)) ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
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
    <script>
        window.addEventListener('load', function() {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            s.onload = initBranches;
            document.body.appendChild(s);

            function initBranches() {
                $('#branch_ids').select2({
                    placeholder: 'Select Branches',
                    width: '100%',
                    closeOnSelect: false
                });

                function toggleBranches() {
                    const name = ($('#role option:selected').data('name') || '').toString();
                    $('#branch-wrapper').toggle(name !== '' && name !== 'admin');
                }

                $('#role').on('change', toggleBranches);
                toggleBranches();
            }
        });
    </script>
    @include('components.preview-img-form')
@endsection
