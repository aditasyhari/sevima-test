<?php

use App\Http\Controllers\Admin\TagihanController as AdminTagihanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Simulator\GatewaySimulatorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Central Dashboard Route (Dispatches to role-specific dashboard)
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

// Admin Keuangan Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tagihan', [AdminTagihanController::class, 'index'])->name('tagihan.index');
    Route::post('/tagihan', [AdminTagihanController::class, 'store'])->name('tagihan.store');
});

// Mahasiswa Routes
Route::middleware(['auth'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
});

// Mock Payment Gateway Simulator (Available for Evaluators & Demo)
Route::get('/simulator', [GatewaySimulatorController::class, 'index'])->name('simulator.index');
Route::post('/simulator/trigger', [GatewaySimulatorController::class, 'trigger'])->name('simulator.trigger');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
