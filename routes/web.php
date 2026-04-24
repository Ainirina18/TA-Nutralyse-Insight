<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ChildProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {return view('auth.login');});
Route::post('/login', [AuthController::class, 'login']); 


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::post('/dashboard/set-child', function (Illuminate\Http\Request $request) {
    session(['active_child_id' => $request->child_id]);
    return back();
})->name('dashboard.set-child');
Route::post('/dashboard/set-child', [DashboardController::class, 'setChild'])
    ->name('dashboard.set-child');

Route::get('/profile-balita', [ChildProfileController::class, 'index']);
/*Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
