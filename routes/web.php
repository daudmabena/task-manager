<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('systems', \App\Http\Controllers\SystemsController::class)->parameters([
        'systems' => 'system:slug'
    ]);

    // User Account Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserAccountController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\UserAccountController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\UserAccountController::class, 'store'])->name('store');
        Route::get('/{user}', [\App\Http\Controllers\UserAccountController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [\App\Http\Controllers\UserAccountController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\UserAccountController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\UserAccountController::class, 'destroy'])->name('destroy');

        // Role and Permission Management
        Route::get('/roles', [\App\Http\Controllers\UserAccountController::class, 'roles'])->name('roles');
        Route::get('/permissions', [\App\Http\Controllers\UserAccountController::class, 'permissions'])->name('permissions');

        // Role Assignment
        Route::post('/{user}/assign-role', [\App\Http\Controllers\UserAccountController::class, 'assignRole'])->name('assign-role');
        Route::delete('/{user}/revoke-role', [\App\Http\Controllers\UserAccountController::class, 'revokeRole'])->name('revoke-role');

        // Permission Management
        Route::post('/roles/{role}/give-permission', [\App\Http\Controllers\UserAccountController::class, 'givePermissionToRole'])->name('give-permission-to-role');
        Route::delete('/roles/{role}/revoke-permission', [\App\Http\Controllers\UserAccountController::class, 'revokePermissionFromRole'])->name('revoke-permission-from-role');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
