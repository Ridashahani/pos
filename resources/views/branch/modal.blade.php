@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
    $needsBranchSelection = !$authUser->active_branch_id;
    $assignedBranches = $authUser->branches()
        ->where(function ($q) {
            $q->where('status', 'Active')->orWhere('status', 1)->orWhere('status', true);
        })
        ->orderBy('name')
        ->get();
@endphp

{{-- Branch Selection Modal --}}
<div class="modal fade" id="branchSelectionModal" tabindex="-1" role="dialog"
    data-backdrop="static" data-keyboard="false" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            {{-- Accent bar --}}
            <div style="height: 5px; background: linear-gradient(90deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);"></div>

            <div class="modal-body p-0">
                <div class="text-center pt-4 pb-2 px-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width:60px; height:60px; background: rgba(59,130,246,.1); color:#3b82f6;">
                        <svg style="width:30px;height:30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h5 class="font-weight-bold mb-1" id="branchModalLabel" style="color:#1e293b;">Select Active Branch</h5>
                    <p class="text-muted mb-0" style="font-size:.88rem;">
                        Please select the branch you are working in to continue.
                    </p>
                </div>

                <div class="px-4 pb-4 pt-3">
                    {{-- Errors --}}
                    <div id="branch-modal-error" class="alert alert-danger d-none" role="alert"></div>

                    @if ($assignedBranches->isEmpty())
                        <div class="alert alert-warning text-center">
                            <strong>No branches assigned.</strong><br>
                            <small>Contact your administrator to assign a branch to your account.</small>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-block">Sign Out</button>
                        </form>
                    @else
                        <form id="branch-modal-form" action="{{ route('branch.set-active') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_intended" id="branch-modal-intended" value="">

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-2" style="font-size:.85rem;">
                                    Your Assigned Branches
                                </label>
                                <select name="branch_id" id="branch-modal-select"
                                    class="form-control" required autofocus
                                    style="border-radius:10px; border:1.5px solid #cbd5e1; height:44px;">
                                    <option value="" disabled selected>— Choose a branch —</option>
                                    @foreach ($assignedBranches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $authUser->active_branch_id == $branch->id ? 'selected' : ($assignedBranches->count() === 1 ? 'selected' : '') }}>
                                            {{ $branch->name }}{{ $branch->address ? ' ('.$branch->address.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" id="branch-modal-submit"
                                class="btn btn-primary btn-block font-weight-bold"
                                style="border-radius:10px; height:44px; font-size:.95rem;">
                                Confirm &amp; Continue &rarr;
                            </button>
                        </form>

                        {{-- Only show Cancel if user already has an active branch (just switching) --}}
                        @if ($authUser->active_branch_id)
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-link text-muted btn-sm"
                                    data-dismiss="modal">Cancel</button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const NEEDS_BRANCH = {{ $needsBranchSelection ? 'true' : 'false' }};
    const IS_ADMIN     = false; // already excluded at blade level

    // URLs to ignore — clicking these should never trigger the modal
    const IGNORED_PATTERNS = [
        /^\/select-branch/,
        /^\/logout/,
        /^\/dashboard(\/|$|\?|#|$)/,
        /^\/profile/,
        /^\/#/,
        /^#/,
        /^javascript/i,
    ];

    function isIgnoredHref(href) {
        if (!href || href === '#' || href.startsWith('javascript')) return true;
        try {
            const url  = new URL(href, window.location.origin);
            const path = url.pathname;
            return IGNORED_PATTERNS.some(re => re.test(path));
        } catch (_) {
            return false;
        }
    }

    function showBranchModal(intendedUrl) {
        document.getElementById('branch-modal-intended').value = intendedUrl || '';
        $('#branchSelectionModal').modal('show');
    }

    // Intercept ALL anchor clicks when no active branch is set
    document.addEventListener('click', function (e) {
        if (!NEEDS_BRANCH) return;

        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (isIgnoredHref(href)) return;

        // Stop navigation — show branch picker first
        e.preventDefault();
        e.stopImmediatePropagation();

        const fullUrl = anchor.href; // resolved absolute URL
        showBranchModal(fullUrl);
    }, true); // capture phase so it fires before Bootstrap collapse etc.

    // No auto-open on load — modal only triggers when user clicks a navigation link

    // Handle form submit — redirect to intended URL on success
    const form = document.getElementById('branch-modal-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const branchId = document.getElementById('branch-modal-select').value;
            if (!branchId) {
                document.getElementById('branch-modal-error').textContent = 'Please select a branch.';
                document.getElementById('branch-modal-error').classList.remove('d-none');
                return;
            }

            const intended  = document.getElementById('branch-modal-intended').value;
            const submitBtn = document.getElementById('branch-modal-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';

            const data = new FormData(form);

            fetch('{{ route("branch.set-active") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
            })
            .then(res => {
                if (res.ok || res.redirected) {
                    // Branch set — reload to intended page or current page
                    window.location.href = intended || window.location.href;
                } else {
                    return res.json().then(json => {
                        throw new Error(json.message || json.errors?.branch_id?.[0] || 'Failed to set branch.');
                    });
                }
            })
            .catch(err => {
                const errEl = document.getElementById('branch-modal-error');
                errEl.textContent = err.message;
                errEl.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm & Continue →';
            });
        });
    }
})();
</script>
