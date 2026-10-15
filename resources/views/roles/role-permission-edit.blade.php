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

        .pswitch input:indeterminate+.slider {
            background-color: #9db8f5;
        }

        .permission-matrix th:first-child,
        .permission-matrix td:first-child {
            width: 22%;
            min-width: 180px;
        }

        .permission-matrix th:not(:first-child),
        .permission-matrix td:not(:first-child) {
            width: 10%;
            min-width: 78px;
            text-align: center;
        }

        .permission-matrix {
            width: 100%;
            min-width: 980px;
            table-layout: fixed;
        }

        .permission-matrix .pswitch {
            vertical-align: middle;
        }

        .permission-action {
            font-weight: 600;
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

                            <label class="mb-2 d-block">Permissions</label>

                            <div class="table-responsive rounded border">
                                <table class="table perm-table permission-matrix mb-0">
                                    <thead>
                                        <tr>
                                            <th>Module</th>
                                            @foreach (['View', 'Create', 'Edit', 'Delete', 'All'] as $label)
                                                <th>
                                                    <div class="permission-action">{{ $label }}</div>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permission_groups as $permission_group)
                                            @php
                                                $g = $permission_group->group_name;
                                                $groupPermissions = $permissions->where('group_name', $g);
                                                $permissionsByAction = $groupPermissions->keyBy(
                                                    fn($permission) => explode('.', $permission->name, 2)[0],
                                                );
                                            @endphp

                                            @if ($groupPermissions->count())
                                                <tr>
                                                    <td class="module-name">
                                                        {{ \Illuminate\Support\Str::headline($g) }}
                                                    </td>
                                                    @foreach (['access', 'create', 'edit', 'delete'] as $action)
                                                        @php($permission = $permissionsByAction->get($action))
                                                        <td>
                                                            @if ($permission)
                                                                <label class="pswitch mb-0">
                                                                    <input type="checkbox" class="perm-toggle"
                                                                        name="permission_id[]" value="{{ $permission->id }}"
                                                                        data-group="{{ $g }}"
                                                                        data-action="{{ $action }}"
                                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                    <span class="slider"></span>
                                                                </label>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td>
                                                        <label class="pswitch mb-0">
                                                            <input type="checkbox" class="module-toggle"
                                                                data-group="{{ $g }}"
                                                                aria-label="Toggle all {{ $g }} permissions">
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
            function syncToggles() {
                $('.module-toggle').each(function() {
                    var $permissions = $('.perm-toggle[data-group="' + $(this).data('group') + '"]');
                    var selected = $permissions.filter(':checked').length;
                    $(this).prop('checked', $permissions.length > 0 && selected === $permissions.length)
                        .prop('indeterminate', selected > 0 && selected < $permissions.length);
                });

                $('.column-toggle').each(function() {
                    var $permissions = $('.perm-toggle[data-action="' + $(this).data('action') + '"]');
                    var selected = $permissions.filter(':checked').length;
                    $(this).prop('checked', $permissions.length > 0 && selected === $permissions.length)
                        .prop('indeterminate', selected > 0 && selected < $permissions.length);
                });

                var $all = $('.perm-toggle');
                var selectedAll = $all.filter(':checked').length;
                $('#enable-all').prop('checked', $all.length > 0 && selectedAll === $all.length)
                    .prop('indeterminate', selectedAll > 0 && selectedAll < $all.length);
            }

            $('.perm-toggle').on('change', function() {
                syncToggles();
            });

            $('.module-toggle').on('change', function() {
                $('.perm-toggle[data-group="' + $(this).data('group') + '"]').prop('checked', this.checked);
                syncToggles();
            });
            syncToggles();
            $('form').on('reset', function() {
                setTimeout(syncToggles, 0);
            });
        });
    </script>
@endsection
