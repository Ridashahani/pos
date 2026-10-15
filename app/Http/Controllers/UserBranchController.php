<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class UserBranchController extends Controller
{
    public function index()
    {
        $users = User::with('branches')->paginate(10);
        return view('user_branch.index', compact('users'));
    }

    public function edit(User $user)
    {
        $branches = Branch::where('status', 'Active')->orWhere('status', 1)->orWhere('status', true)->orderBy('name')->get();
        $assigned = $user->branches->pluck('id')->toArray();

        return view('user_branch.edit', compact('user', 'branches', 'assigned'));
    }
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'branch_ids'   => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);

        $user->branches()->sync($data['branch_ids'] ?? []);

        $user->unsetRelation('branches');
        $user->unsetRelation('roles');
        $allowedBranchIds = $user->allowedBranches()->pluck('id')->toArray();
        if ($user->active_branch_id && !in_array($user->active_branch_id, $allowedBranchIds)) {
            $user->active_branch_id = null;
            $user->save();
        }

        return redirect()->route('user-branch.index')
            ->with('success', 'Branches assign ho gayi.');
    }
    public function destroy(User $user, Branch $branch)
    {
        $user->branches()->detach($branch->id);

        if ($user->active_branch_id == $branch->id) {
            $user->active_branch_id = null;
            $user->save();
        }

        return back()->with('success', 'Branch remove ho gayi.');
    }
}
