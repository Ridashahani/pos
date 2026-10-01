<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBranch
{
    /**
     * Excluded route names from branch enforcement.
     */
    protected array $excludedRouteNames = [
        'branch.select',
        'branch.set-active',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = $request->user();

        // Admin has access to ALL branches, no selection popup needed
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if current route is excluded by name or path
        $routeName = $request->route() ? $request->route()->getName() : null;
        if ($routeName && in_array($routeName, $this->excludedRouteNames)) {
            return $next($request);
        }

        if ($request->is('select-branch*') || $request->is('logout')) {
            return $next($request);
        }

        // Validate active_branch_id against user's assigned branches (user_branch table)
        if ($user->active_branch_id) {
            $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if (!in_array($user->active_branch_id, $assignedBranchIds)) {
                $user->active_branch_id = null;
                $user->save();
            }
        }

        // If user has no active branch, block access and redirect to branch selection popup
        if (!$user->active_branch_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Active branch selection required.',
                    'redirect' => route('branch.select'),
                ], 403);
            }

            // Save intended URL for GET requests so user returns to original page after selection
            if ($request->isMethod('GET') && !$request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('branch.select');
        }

        return $next($request);
    }
}
