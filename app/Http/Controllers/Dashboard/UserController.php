<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return view('users.index', [
            'users' => QueryBuilder::for(User::class)
                ->with('roles')
                ->with(['roles', 'branches'])
                ->allowedSorts(['name', 'username', 'email'])
                ->filter(request(['search']))
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create', [
            'roles'    => Role::all(),
            'branches' => Branch::where('status', 'Active')->orWhere('status', 1)->orWhere('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $request->validate([
            'branch_ids'   => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($request->password);

        /**
         * Handle upload image with Storage.
         */
        if ($file = $request->file('photo')) {
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $path = 'public/profile/';

            $file->storeAs($path, $fileName);
            $validatedData['photo'] = $fileName;
        }

        $user = User::create($validatedData);

        if ($request->role) {
            $user->assignRole($request->role);
            $this->syncBranches($request, $user);
        }

        return Redirect::route('users.index')->with('success', 'New User has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            'userData' => $user,
            'roles' => Role::all(),
            'branches' => Branch::where('status', 'Active')->orWhere('status', 1)->orWhere('status', true)->orderBy('name')->get(),
            'assigned' => $user->branches->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        // Remove password from validated data if not provided (Request handles validation, but we need to check if we should hash it)
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        } else {
            unset($validatedData['password']);
            unset($validatedData['password_confirmation']);
        }

        /**
         * Handle upload image with Storage.
         */
        if ($file = $request->file('photo')) {
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $path = 'public/profile/';

            /**
             * Delete photo if exists.
             */
            if ($user->photo) {
                Storage::delete($path . $user->photo);
            }

            $file->storeAs($path, $fileName);
            $validatedData['photo'] = $fileName;
        }

        $user->update($validatedData);

        if ($request->role) {
            $user->syncRoles($request->role);
            $this->syncBranches($request, $user);
        }

        return Redirect::route('users.index')->with('success', 'User has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        /**
         * Delete photo if exists.
         */
        if ($user->photo) {
            Storage::delete('public/profile/' . $user->photo);
        }

        User::destroy($user->id);

        return Redirect::route('users.index')->with('success', 'User has been deleted!');
    }

    private function syncBranches(Request $request, User $user): void
    {
        $role = $request->role ? (is_numeric($request->role) ? Role::find($request->role) : Role::where('name', $request->role)->first()) : null;
        $isAdmin = $role && strtolower($role->name) === 'admin';

        // Admin has access to all branches, other roles sync selected branches
        $user->branches()->sync($isAdmin ? [] : ($request->branch_ids ?? []));
    }
}
