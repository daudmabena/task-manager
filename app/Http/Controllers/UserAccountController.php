<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\AssignRoleRequest;
use App\Http\Requests\User\RevokeRoleRequest;
use App\Http\Requests\User\GivePermissionToRoleRequest;
use App\Http\Requests\User\RevokePermissionFromRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class UserAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
        $this->middleware('permission:assign roles')->only(['assignRole', 'revokeRole']);
        $this->middleware('permission:manage permissions')->only(['givePermissionToRole', 'revokePermissionFromRole']);
    }

    /**
     * Display a listing of users with their roles and permissions.
     */
    public function index(Request $request): Response
    {
        $query = User::with(['roles', 'permissions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when($request->sort_by, function ($query, $sortBy) use ($request) {
                $direction = $request->sort_direction === 'desc' ? 'desc' : 'asc';
                $query->orderBy($sortBy, $direction);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            });

        $users = $query->paginate(10)->withQueryString();

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'role', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => $request->email_verified ? now() : null,
            ]);

            // Assign roles if provided
            if ($request->roles) {
                $user->assignRole($request->roles);
            }

            // Assign direct permissions if provided
            if ($request->permissions) {
                $user->givePermissionTo($request->permissions);
            }

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User created');

            DB::commit();

            return Redirect::route('users.index')
                ->with('success', 'User created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load(['roles.permissions', 'permissions']);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $user->load(['roles', 'permissions']);
        $roles = Role::all();
        $permissions = Permission::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => $request->email_verified ? now() : null,
            ];

            // Only update password if provided
            if ($request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Sync roles
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            // Sync direct permissions
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User updated');

            DB::commit();

            return Redirect::route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            // Prevent deleting the current user
            if ($user->id === auth()->id()) {
                return Redirect::back()
                    ->with('error', 'You cannot delete your own account.');
            }

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User deleted');

            $user->delete();

            DB::commit();

            return Redirect::route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Assign roles to a user.
     */
    public function assignRole(AssignRoleRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $user->assignRole($request->roles);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('Roles assigned to user');

            DB::commit();

            return Redirect::back()
                ->with('success', 'Roles assigned successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to assign roles: ' . $e->getMessage());
        }
    }

    /**
     * Revoke roles from a user.
     */
    public function revokeRole(RevokeRoleRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $user->removeRole($request->roles);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('Roles revoked from user');

            DB::commit();

            return Redirect::back()
                ->with('success', 'Roles revoked successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to revoke roles: ' . $e->getMessage());
        }
    }

    /**
     * Give permissions to a role.
     */
    public function givePermissionToRole(GivePermissionToRoleRequest $request, $roleId)
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($roleId);
            $role->givePermissionTo($request->permissions);

            activity()
                ->performedOn($role)
                ->causedBy(auth()->user())
                ->log('Permissions given to role');

            DB::commit();

            return Redirect::back()
                ->with('success', 'Permissions assigned to role successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to assign permissions: ' . $e->getMessage());
        }
    }

    /**
     * Revoke permissions from a role.
     */
    public function revokePermissionFromRole(RevokePermissionFromRoleRequest $request, $roleId)
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($roleId);
            $role->revokePermissionTo($request->permissions);

            activity()
                ->performedOn($role)
                ->causedBy(auth()->user())
                ->log('Permissions revoked from role');

            DB::commit();

            return Redirect::back()
                ->with('success', 'Permissions revoked from role successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to revoke permissions: ' . $e->getMessage());
        }
    }

    /**
     * Display roles management page.
     */
    public function roles(): Response
    {
        $roles = Role::with('permissions')->paginate(10);
        $permissions = Permission::all();

        return Inertia::render('Users/Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Display permissions management page.
     */
    public function permissions(): Response
    {
        $permissions = Permission::paginate(10);

        return Inertia::render('Users/Permissions', [
            'permissions' => $permissions,
        ]);
    }
}
