<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BuildingController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FloorController;
use App\Http\Controllers\Web\InvestorController;
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\ShareholderController;
use App\Http\Controllers\Web\UnitController;
use App\Http\Controllers\Web\UnitTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('landing');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'web.company'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)->except(['show']);

    // Unit Types
    Route::resource('unit-types', UnitTypeController::class)->except(['show']);

    // Buildings
    Route::resource('buildings', BuildingController::class)->except(['show']);

    // Floors
    Route::resource('floors', FloorController::class)->except(['show']);

    // Units
    Route::resource('units', UnitController::class)->except(['show']);

    // Members
    Route::resource('members', MemberController::class)->except(['show']);

    // Shareholders
    Route::resource('shareholders', ShareholderController::class)->except(['show']);

    // Investors
    Route::resource('investors', InvestorController::class)->except(['show']);

    // Customers
    Route::resource('customers', CustomerController::class)->except(['show']);
});
