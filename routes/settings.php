<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\UserRoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');
    
    // User Role Management Routes
    Route::prefix('settings/users')->name('users.')->middleware('can:manage users')->group(function () {
        Route::get('/', [UserRoleController::class, 'index'])->name('index');
        Route::get('/{user}', [UserRoleController::class, 'show'])->name('show');
        
        // Role management
        Route::post('/{user}/roles', [UserRoleController::class, 'assignRole'])->name('assign-role');
        Route::delete('/{user}/roles', [UserRoleController::class, 'removeRole'])->name('remove-role');
        
        // Permission management
        Route::post('/{user}/permissions', [UserRoleController::class, 'assignPermission'])->name('assign-permission');
        Route::delete('/{user}/permissions', [UserRoleController::class, 'removePermission'])->name('remove-permission');
        
        // Bulk operations
        Route::post('/bulk/assign-role', [UserRoleController::class, 'bulkAssignRole'])->name('bulk-assign-role');
    });
});
