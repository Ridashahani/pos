<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSelectionController extends Controller
{
    /**
     * Display the active branch selection modal view.
     */
    public function select(Request $request)
    {
        $user = $request->user();

        // Admin has access to all branches, no selection needed
        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        // Only show branches assigned to user via user_branch table
        $branches = $user->branches()
            ->where(function ($q) {
                $q->where('status', 'Active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            })
            ->orderBy('name')
            ->get();

        return view('branch.select', compact('user', 'branches'));
    }

    /**
     * Set the active branch for the authenticated user and redirect to intended URL.
     */
    public function setActive(Request $request)
    {
        $user = $request->user();

        // Admin doesn't need branch selection
        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        // Verify branch is assigned to user via user_branch table
        $assignedBranch = $user->branches()
            ->where('branches.id', (int) $request->branch_id)
            ->first();

        if (!$assignedBranch) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'The selected branch is not assigned to your account.',
                ], 422);
            }
            return back()->withErrors([
                'branch_id' => 'The selected branch is not assigned to your account or is inactive.',
            ])->withInput();
        }

        $user->active_branch_id = $assignedBranch->id;
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Active branch set to {$assignedBranch->name}.",
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', "Active branch set to {$assignedBranch->name}.");
    }
}
