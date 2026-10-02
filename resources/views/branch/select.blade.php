<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Select Active Branch - POSDash</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/backend.css?v=1.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .branch-modal-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-top-accent {
            height: 6px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
        }

        .branch-header {
            padding: 30px 32px 20px;
            text-align: center;
        }

        .branch-icon-wrapper {
            width: 68px;
            height: 68px;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .branch-icon-wrapper svg {
            width: 36px;
            height: 36px;
        }

        .branch-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .branch-subtitle {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .branch-body {
            padding: 10px 32px 30px;
        }

        .custom-select-lg {
            height: calc(1.5em + 1.25rem + 8px);
            font-size: 1rem;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            padding: 0.625rem 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .custom-select-lg:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn-confirm {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-confirm:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .user-chip {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 18px;
            font-weight: 500;
        }

        .user-chip-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="branch-modal-card">
        <div class="modal-top-accent"></div>

        <div class="branch-header">
            <div class="branch-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
            </div>
            <h3 class="branch-title">Select Active Branch</h3>
            <p class="branch-subtitle">
                Please choose the branch you are currently working in. All transactions and actions will be recorded under this branch.
            </p>
        </div>

        <div class="branch-body">
            <div class="text-center">
                <div class="user-chip">
                    <span class="user-chip-dot"></span>
                    Logged in as: <strong>&nbsp;{{ $user->name }}</strong>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($branches->isEmpty())
                <div class="alert alert-warning text-center" role="alert">
                    <h6 class="font-weight-bold mb-1">No Assigned Branches Found</h6>
                    <p class="mb-0 font-size-13 text-muted">
                        Your account has not been assigned to any active branch. Please contact your system administrator to assign a branch.
                    </p>
                </div>
                <div class="mt-4">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-block py-2">
                            Sign Out
                        </button>
                    </form>
                </div>
            @else
                <form action="{{ route('branch.set-active') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="branch_id" class="font-weight-bold text-dark mb-2">Available Branches</label>
                        <select name="branch_id" id="branch_id" class="form-control custom-select-lg" required autofocus>
                            <option value="" disabled {{ !$user->active_branch_id ? 'selected' : '' }}> Choose Branch </option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ old('branch_id', $user->active_branch_id) == $branch->id ? 'selected' : ($branches->count() === 1 ? 'selected' : '') }}>
                                    {{ $branch->name }}{{ $branch->address ? ' (' . $branch->address . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-confirm btn-block mb-3">
                        Confirm Branch & Continue &rarr;
                    </button>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        @if ($user->active_branch_id)
                            <a href="{{ route('dashboard') }}" class="text-muted font-size-14 text-decoration-none">
                                &larr; Return to Dashboard
                            </a>
                        @else
                            <span class="text-muted font-size-13">Branch selection is required to proceed</span>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger p-0 font-size-13 text-decoration-none">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>

</html>
