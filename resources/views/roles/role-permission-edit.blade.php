@extends('dashboard.body.main')

@section('specificpagestyles')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <style>
        .role-name-box {
            background: #f4f6fa;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 600;
            max-width: 420px;
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

        /* toggle switch look-and-feel */
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
                            <h4 class="card-title">Edit Role</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('rolePermission.update', $role->id) }}" method="POST" id="rolePermissionForm">
                            @csrf
                            @method('put')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="mb-1">Role Name</label>
                                    <div class="role-name-box">{{ $role->name }}</div>
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
                                                $permission = $permissions->firstWhere('group_name', $permission_group->group_name);
                                            @endphp

                                            @if ($permission)
                                                <tr>
                                                    <td class="module-name">{{ $permission_group->group_name }}</td>
                                                    <td class="text-center">
                                                        <label class="pswitch mb-0">
                                                            <input type="checkbox" class="perm-toggle"
                                                                id="permission_id[{{ $permission->id }}]"
                                                                name="permission_id[]"
                                                                value="{{ $permission->id }}"
                                                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
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
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
                                </button>
                                <button type="reset" class="btn btn-secondary mr-2">Reset</button>
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

            $('.perm-toggle').trigger('change');
        });
    </script>
@endsection