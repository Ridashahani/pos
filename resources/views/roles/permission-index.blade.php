@extends('dashboard.body.main')

@section('specificpagestyles')
    <style>
        .filter-bar {
            background: #fff;
            border: 1px solid #e4e7ec;
            border-radius: 10px;
            padding: 12px 16px 4px;
        }

        .filter-bar .form-control {
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            height: 40px;
        }

        .filter-bar .form-control:focus {
            border-color: #2f6fed;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, .12);
        }

        .perm-table {
            table-layout: fixed;
            width: 100%;
        }

        .perm-table th:nth-child(1),
        .perm-table td:nth-child(1) {
            width: 10%;
        }

        .perm-table th:nth-child(2),
        .perm-table td:nth-child(2) {
            width: 30%;
            padding-left: 60px !important;
        }

        .perm-table th:nth-child(3),
        .perm-table td:nth-child(3) {
            width: 30%;
            padding-left: 60px !important;
        }

        .perm-table th:nth-child(4),
        .perm-table td:nth-child(4) {
            width: 30%;
            text-align: center;
        }

        .module-badge {
            background: #eef3ff;
            color: #2f6fed;
            font-weight: 600;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 999px;
        }

        .action-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .action-view {
            background: #e8f1ff;
            color: #2f6fed;
        }

        .action-create {
            background: #e6f7ee;
            color: #1e9e5a;
        }

        .action-edit {
            background: #fff4e0;
            color: #d98300;
        }

        .action-delete {
            background: #fdeaea;
            color: #d93025;
        }

        .action-custom {
            background: #f1f2f4;
            color: #6b7280;
        }

        .perm-key {
            background: #f4f6fa;
            color: #374151;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12.5px;
        }

        .list-action .btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
    </style>
@endsection

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Permission List</h4>
                    </div>
                    <div>
                        @can('create.roles')
                            <a href="{{ route('permission.create') }}" class="btn btn-primary add-list"><x-heroicon-o-plus
                                    class="w-5 h-5 mr-3" />Create Permission</a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="module-toolbar d-flex flex-wrap align-items-center mb-3">
                    <form action="{{ route('permission.index') }}" method="GET" class="d-flex flex-wrap align-items-center">
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="search" name="search" class="form-control" placeholder="Search permission or module..." value="{{ request('search') }}">
                        </div>
                        <div class="mr-2 mb-2 mb-md-0" style="min-width: 180px;">
                            <select name="group" class="form-control">
                                <option value="">All modules</option>
                                @foreach ($permissionGroups as $group)
                                    <option value="{{ $group }}" @selected(request('group') === $group)>
                                        {{ \Illuminate\Support\Str::headline($group) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </button>
                        @if (request()->filled('search') || request()->filled('group'))
                            <a href="{{ route('permission.index') }}" class="btn btn-light mr-2 mb-2 mb-md-0" title="Clear filters">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </a>
                        @endif
                    </form>
                    @if (isset($permissions) && method_exists($permissions, 'total'))
                    <span class="badge badge-light border px-3 py-2">{{ $permissions->total() }} records</span>
                    @endif
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive rounded mb-3">
                            <table class="table perm-table mb-0">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Module</th>
                                        <th>Type</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($permissions as $permission)
                                        @php
                                            $permissionAction = explode('.', $permission->name, 2)[0];
                                            $actionLabels = [
                                                'access' => 'View',
                                                'create' => 'Create',
                                                'edit' => 'Edit',
                                                'delete' => 'Delete',
                                            ];
                                            $actionClasses = [
                                                'access' => 'action-view',
                                                'create' => 'action-create',
                                                'edit' => 'action-edit',
                                                'delete' => 'action-delete',
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $permissions->firstItem() + $loop->index }}</td>
                                            <td><span
                                                    class="module-badge">{{ \Illuminate\Support\Str::headline($permission->group_name) }}</span>
                                            </td>
                                            <td><span
                                                    class="action-badge {{ $actionClasses[$permissionAction] ?? 'action-custom' }}">{{ $actionLabels[$permissionAction] ?? 'Custom' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-center list-action">
                                                    @can('edit.roles')
                                                        <a class="btn btn-primary mr-2" data-toggle="tooltip"
                                                            data-placement="top" title="Edit"
                                                            href="{{ route('permission.edit', $permission->id) }}">
                                                            <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                        </a>
                                                    @endcan
                                                    @can('delete.roles')
                                                        <form action="{{ route('permission.destroy', $permission->id) }}"
                                                            method="POST" style="display:inline;">
                                                            @method('delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger border-0"
                                                                onclick="return confirm('Are you sure you want to delete this permission?')"
                                                                data-toggle="tooltip" data-placement="top" title="Delete">
                                                                <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <div class="alert text-white bg-danger" role="alert">
                                                    <div class="iq-alert-text">Data not Found.</div>
                                                    <button type="button" class="close" data-dismiss="alert"
                                                        aria-label="Close">
                                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
  