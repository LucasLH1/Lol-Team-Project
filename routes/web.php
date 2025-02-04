<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/admin/roles/{user}', [RoleController::class, 'assignRole'])->name('roles.assign');
});
