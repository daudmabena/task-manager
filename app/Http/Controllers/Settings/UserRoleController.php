<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display role management interface.
     */
    public function index(): Response
    {
        $this->authorize('manage users');
        
        $users = User::with(['roles', 'permissions'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15)
            ->withQueryString();

        $roles = Role::all();
        $permissions = Permission::all();

        return Inertia::render('Settings/UserRoles/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage users');
        
        $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        if (!$user->hasRole($request->role)) {
            $user->assignRole($request->role);
            
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'role' => $request->role,
                    'action' => 'assigned'
                ])
                ->log('Role assigned to user');
        }

        return back()->with('success', "Role '{$request->role}' assigned to {$user->name}.");
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage users');
        
        $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        if ($user->hasRole($request->role)) {
            $user->removeRole($request->role);
            
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'role' => $request->role,
                    'action' => 'removed'
                ])
                ->log('Role removed from user');
        }

        return back()->with('success', "Role '{$request->role}' removed from {$user->name}.");
    }

    /**
     * Assign permission to user.
     */
    public function assignPermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage users');
        
        $request->validate([
            'permission' => ['required', 'string', Rule::exists('permissions', 'name')],
        ]);

        if (!$user->hasPermissionTo($request->permission)) {
            $user->givePermissionTo($request->permission);
            
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permission' => $request->permission,
                    'action' => 'assigned'
                ])
                ->log('Permission assigned to user');
        }

        return back()->with('success', "Permission '{$request->permission}' assigned to {$user->name}.");
    }

    /**
     * Remove permission from user.
     */
    public function removePermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage users');
        
        $request->validate([
            'permission' => ['required', 'string', Rule::exists('permissions', 'name')],
        ]);

        if ($user->hasPermissionTo($request->permission)) {
            $user->revokePermissionTo($request->permission);
            
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permission' => $request->permission,
                    'action' => 'removed'
                ])
                ->log('Permission removed from user');
        }

        return back()->with('success', "Permission '{$request->permission}' removed from {$user->name}.");
    }

    /**
     * Get user's detailed role and permission information.
     */
    public function show(User $user): Response
    {
        $this->authorize('manage users');
        
        $user->load(['roles', 'permissions']);
        
        $allRoles = Role::with('permissions')->get();
        $allPermissions = Permission::all();
        
        // Get permissions via roles
        $permissionsViaRoles = $user->getPermissionsViaRoles();
        
        // Get direct permissions
        $directPermissions = $user->getDirectPermissions();
        
        return Inertia::render('Settings/UserRoles/Show', [
            'user' => $user,
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
            'permissionsViaRoles' => $permissionsViaRoles,
            'directPermissions' => $directPermissions,
        ]);
    }

    /**
     * Bulk assign roles to multiple users.
     */
    public function bulkAssignRole(Request $request): RedirectResponse
    {
        $this->authorize('manage users');
        
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', Rule::exists('users', 'id')],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        DB::transaction(function () use ($request) {
            $users = User::whereIn('id', $request->user_ids)->get();
            $assignedCount = 0;
            
            foreach ($users as $user) {
                if (!$user->hasRole($request->role)) {
                    $user->assignRole($request->role);
                    $assignedCount++;
                    
                    activity()
                        ->performedOn($user)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'role' => $request->role,
                            'action' => 'bulk_assigned'
                        ])
                        ->log('Role bulk assigned to user');
                }
            }
            
            return $assignedCount;
        });

        return back()->with('success', "Role '{$request->role}' assigned to multiple users.");
    }
}
