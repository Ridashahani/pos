@extends('dashboard.body.main')

@section('specificpagestyles')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <style>
        .role-select {
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            padding: 10px 14px;
            max-width: 420px;
        }

        .role-select:focus {
            outline: none;
            border-color: #2f6fed;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, .12);
        }

        .perm-table thead th {
            background: #f8f9fb;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .04em;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
        }

        .perm-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #eef0f3;
            vertical-align: middle;
        }

        .perm-table tbody tr:hover {
            background: #fafbfc;
        }

        .module-name {
            font-weight: 500;
            color: #1f2937;
            text-transform: capitalize;
        }

        .pswitch {
            display: inline-block;
            width: 42px;
            height: 22px;
            position: relative;
        }

        .pswitch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .pswitch .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #d7dbe0;
            border-radius: 999px;
            transition: .15s;
        }

        .pswitch .slider:before {
            content: "";
            position: absolute;
            height: 16px;
            width: 16px;
            left: 3px;
            top: 3px;
            background-color: #fff;
            border-radius: 50%;
            transition: .15s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .25);
        }

        .pswitch input:checked+.slider {
            background-color: #2f6fed;
        }

        .pswitch input:checked+.slider:before {
            transform: translateX(20px);
        }

        .enable-all-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #4b5563;
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
                            <h4 class="card-title">Assign Permissions</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('rolePermission.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="role_id">Role Name <span class="text-danger">*</span></label>
                                    <select class="form-control role-select @error('role_id') is-invalid @enderror"
                                        id="role_id" name="role_id" required>
                                        <option selected="" disabled>Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">Permissions</label>
                                <div class="enable-all-wrap">
                                    <span>Enable All</span>
                                    <label class="pswitch mb-0">
                                        <input type="checkbox" id="enable-all">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="table-responsive rounded border">
                                <table class="table perm-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Module</th>
                                            <th class="text-center">Access</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permission_groups as $permission_group)
                                            @php
                                                $groupPermissions = $permissions_by_group[$permission_group->group_name] ?? [];
                                                $permission = collect($groupPermissions)->first();
                                            @endphp

                                            @if ($permission)
                                                <tr>
                                                    <td class="module-name">{{ $permission_group->group_name }}</td>
                                                    <td class="text-center">
                                                        <label class="pswitch mb-0">
                                                            <input type="checkbox" class="perm-toggle"
                                                                id="permission_id[{{ $permission->id }}]"
                                                                name="permission_id[]"
                                                                value="{{ $permission->id }}">
                                                            <span class="slider"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-right">
                                <button type="submit" class="btn btn-save mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                                </button>
                                <a class="btn btn-cancel" href="{{ route('rolePermission.index') }}">
                                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('#enable-all').on('change', function() {
                $('.perm-toggle').prop('checked', $(this).is(':checked'));
            });

            $('.perm-toggle').on('change', function() {
                var $all = $('.perm-toggle');
                $('#enable-all').prop('checked', $all.length === $all.filter(':checked').length);
            });
        });
    </script>
@endsection