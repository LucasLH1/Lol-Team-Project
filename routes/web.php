<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiotController;
use Illuminate\Support\Facades\Cache;

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



Route::middleware(['auth'])->group(function () {
    Route::get('/riot/profile', [RiotController::class, 'showProfile'])->name('riot.profile');
    Route::get('/riot/profile/refresh', [RiotController::class, 'refreshMatches'])->name('riot.refresh');
    Route::get('/riot/profile/loadMore', [RiotController::class, 'loadMoreMatches'])->name('riot.loadMore');
    Route::get('/match/details/{matchId}', [RiotController::class, 'getMatchDetailsAjax']);

});


Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/admin/roles/{user}', [RoleController::class, 'assignRole'])->name('roles.assign');
});
