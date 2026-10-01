<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Non-admin: always reset active branch on login, then redirect to branch selection.
        // Exception: if only ONE branch is assigned → auto-set it and go straight to dashboard.
        if (!$user->isAdmin()) {
            $assignedBranches = $user->branches()
                ->where(function ($q) {
                    $q->where('status', 'Active')->orWhere('status', 1)->orWhere('status', true);
                })
                ->get();

            if ($assignedBranches->count() === 1) {
                // Auto-set the only branch — no manual selection needed
                $user->active_branch_id = $assignedBranches->first()->id;
                $user->save();
            } else {
                // Multiple (or zero) branches → force selection every login
                $user->active_branch_id = null;
                $user->save();
                return redirect()->route('branch.select');
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
