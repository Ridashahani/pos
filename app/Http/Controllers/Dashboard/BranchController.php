<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $branches = Branch::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->get()
            ->map(fn(Branch $branch) => $branch->only([
                'name',
                'code',
                'address',
                'phone',
                'status',
            ]));

        return view('modules.index', [
            'title' => 'Branches',
            'description' => 'Store branches and locations.',
            'module' => 'branches',
            'records' => $branches,
        ]);
    }

    public function create(): View
    {
        return view('modules.create-branch');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }
}
