@extends('dashboard.body.main')

@section('specificpagestyles')
    <style>
        .rl-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .rl-breadcrumb a {
            color: #6b7280;
            text-decoration: none;
        }

        .rl-breadcrumb .active {
            color: #1f2937;
            font-weight: 500;
        }

        .rl-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(16, 24, 40, .06);
        }

        .rl-header {
            padding: 22px 24px 0;
        }

        .rl-header h4 {
            font-weight: 700;
            color: #1f2937;
        }

        .rl-search {
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            padding: 9px 14px;
            width: 260px;
            font-size: 14px;
        }

        .rl-search:focus {
            outline: none;
            border-color: #2f6fed;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, .12);
        }

        .rl-table thead th {
            background: #f8f9fb;
            color: #6b7280;
            font-weight: 600;
            border: none;
            padding: 14px 24px;
        }

        .rl-table tbody td {
            padding: 16px 24px;
            border-top: 1px solid #f0f1f4;
            vertical-align: middle;
            color: #374151;
        }

        .rl-table tbody tr:hover {
            background: #fafbfc;
        }

        .rl-role-name {
            color: #2f6fed;
            font-weight: 600;
        }

        .rl-perm-count {
            color: #2f6fed;
            font-weight: 500;
        }

        .rl-icon-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            color: #fff;
        }

        .rl-icon-btn.edit {
            background: #2fb344;
        }

        .rl-icon-btn.edit:hover {
            background: #259238;
        }

        .rl-icon-btn.delete {
            background: #e6484f;
        }

        .rl-icon-btn.delete:hover {
            background: #c93b41;
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

                <div class="rl-breadcrumb">
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                    <span>›</span>
                    <span class="active">Role List</span>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card rl-card">
                    <div class="rl-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">Role List</h4>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="rlSearch" class="rl-search" placeholder="Search...">
                            <a href="{{ route('rolePermission.create') }}" class="btn btn-primary ml-2">
                                <x-heroicon-o-plus class="w-5 h-5 mr-1 inline" /> Assign Permissions
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive mb-0">
                            <table class="table rl-table mb-0" id="rlTable">
                                <thead>
                                    <tr>
                                        <th>Sr.#</th>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Created Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($roles as $role)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="rl-role-name">{{ $role->name }}</td>
                                            <td class="rl-perm-count">{{ $role->permissions->count() }} permissions</td>
                                            <td>{{ $role->created_at ? $role->created_at->format('d-m-Y') : '-' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a class="rl-icon-btn edit mr-2" data-toggle="tooltip"
                                                        data-placement="top" title="Edit"
                                                        href="{{ route('rolePermission.edit', $role->id) }}">
                                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                                    </a>
                                                    <form action="{{ route('rolePermission.destroy', $role->id) }}"
                                                        method="POST" style="display:inline;">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="rl-icon-btn delete"
                                                            onclick="return confirm('Are you sure you want to delete this role permission assignment?')"
                                                            data-toggle="tooltip" data-placement="top" title="Delete">
                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
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
                        @if ($roles->hasPages())
                            <div class="p-3">
                                {{ $roles->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('rlSearch').addEventListener('keyup', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#rlTable tbody tr').forEach(function(row) {
                var roleName = row.querySelector('.rl-role-name');
                if (!roleName) return;
                row.style.display = roleName.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
@endsection