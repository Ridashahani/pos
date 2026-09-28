@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Create User</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row align-items-start">
                                <div class="form-group col-md-12">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                        id="username" name="username" value="{{ old('username') }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" required autocomplete="off">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="password_confirmation">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        id="password_confirmation" name="password_confirmation" required>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="role">Role</label>
                                    <select class="form-control @error('role') is-invalid @enderror" name="role"
                                        id="role">
                                        <option selected disabled> Select Role </option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" data-name="{{ strtolower($role->name) }}"
                                                {{ old('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}
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
                                        name="branch_ids[]" id="branch_ids" multiple>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ in_array($branch->id, old('branch_ids', [])) ? 'selected' : '' }}>
                                                {{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3 mb-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                                </button>
                                <a class="btn btn-orange" href="{{ route('users.index') }}">
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

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/css/bootstrap-multiselect.min.css">

    <style>
        #branch-wrapper .btn-group,
        #branch-wrapper .multiselect-container {
            width: 100%;
        }

        #branch-wrapper .multiselect {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
            width: 100% !important;
            min-height: 44px;
            height: auto;
            padding: 0 2rem 0 1rem !important;
            white-space: normal;
            line-height: 1.4;
            appearance: none;
            background-color: #fff !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right .75rem center !important;
            background-size: 14px 14px !important;
        }

        #branch-wrapper .multiselect .multiselect-selected-text {
            display: block;
            width: 100%;
            text-align: left !important;
            margin: 0;
            padding: 0;
        }

        #branch-wrapper .multiselect .caret {
            display: none !important;
        }

        #branch-wrapper .multiselect-container label {
            margin-bottom: 0;
            padding: 4px 10px;
            cursor: pointer;
        }
    </style>

    <script>
        window.addEventListener('load', function() {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/js/bootstrap-multiselect.min.js';
            s.onload = initBranches;
            document.body.appendChild(s);

            function initBranches() {
                $('#branch_ids').multiselect({
                    buttonClass: 'form-control',
                    buttonWidth: '100%',
                    buttonText: function(options) {
                        if (!options.length) return 'Select Branches';
                        return options.map(function() {
                            return $(this).text().trim();
                        }).get().join(', ');
                    }
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
