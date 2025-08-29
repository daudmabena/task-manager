<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load(['roles', 'permissions']);
        
        // Get all available roles and permissions for display
        $availableRoles = Role::all();
        $availablePermissions = Permission::all();
        
        // Get user's permissions via roles
        $permissionsViaRoles = $user->getPermissionsViaRoles();
        
        // Check if user can manage roles
        $canManageUsers = $user->hasPermissionTo('manage users');
        
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'userRoles' => $user->roles,
            'userPermissions' => $user->permissions,
            'permissionsViaRoles' => $permissionsViaRoles,
            'availableRoles' => $availableRoles,
            'availablePermissions' => $availablePermissions,
            'canManageUsers' => $canManageUsers,
        ]);
    }

    /**
     * Update the user's profile settings.
     * 
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
