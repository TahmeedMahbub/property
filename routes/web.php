<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BuildingController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocumentDownloadController;
use App\Http\Controllers\Web\FloorController;
use App\Http\Controllers\Web\InvestorController;
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\ProfileController;
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

// Email Verification
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyCode'])->name('verification.code');
    Route::post('/verify-email/resend', [AuthController::class, 'resendCode'])->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'web.company'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::put('/profile/company', [ProfileController::class, 'updateCompany'])->name('profile.company');

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

    // Documents (authorized download/preview)
    Route::get('/documents/{document}/download', [DocumentDownloadController::class, 'download'])->name('documents.auth-download');
    Route::get('/documents/{document}/preview', [DocumentDownloadController::class, 'preview'])->name('documents.preview');
});

/*
|--------------------------------------------------------------------------
| Signed Document Download (no auth required, signature validates access)
|--------------------------------------------------------------------------
*/
Route::get('/documents/{document}/signed', [DocumentDownloadController::class, 'signedDownload'])
    ->name('documents.download');
