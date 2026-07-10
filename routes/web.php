<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BuildingController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocumentDownloadController;
use App\Http\Controllers\Web\FloorController;
use App\Http\Controllers\Web\InvestorController;
use App\Http\Controllers\Web\LoanController;
use App\Http\Controllers\Web\LoanReportController;
use App\Http\Controllers\Web\LoanRepaymentController;
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\PlotBookingController;
use App\Http\Controllers\Web\PlotBookingDocumentController;
use App\Http\Controllers\Web\PlotBookingExpenseController;
use App\Http\Controllers\Web\PlotBookingPaymentController;
use App\Http\Controllers\Web\PlotController;
use App\Http\Controllers\Web\PlotDocumentController;
use App\Http\Controllers\Web\PlotPersonImageController;
use App\Http\Controllers\Web\PlotPaymentController;
use App\Http\Controllers\Web\PlotReportController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\PublicCustomerProfileController;
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
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

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
    Route::get('/investments', [ShareholderController::class, 'investments'])->name('shareholders.investments');
    Route::get('/shareholders/{uuid}/investment', [ShareholderController::class, 'investment'])->name('shareholders.investment');
    Route::post('/shareholders/{uuid}/transaction', [ShareholderController::class, 'transact'])->name('shareholders.transaction');
    Route::resource('shareholders', ShareholderController::class)->except(['show']);

    // Investors
    Route::resource('investors', InvestorController::class)->except(['show']);
    Route::delete('/customers/{uuid}/documents/{document}', [CustomerController::class, 'destroyDocument'])->name('customers.documents.destroy');
    Route::post('/customers/{uuid}/profile-link/regenerate', [CustomerController::class, 'regenerateProfileLink'])->name('customers.profile-link.regenerate');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Plots (land acquisition)
    Route::get('/plots/reports', [PlotReportController::class, 'index'])->name('plots.reports');
    Route::get('/plots/reports/{type}', [PlotReportController::class, 'show'])->name('plots.reports.show');
    Route::get('/plots/people/{type}/{uuid}/{field}', [PlotPersonImageController::class, 'show'])->name('plots.people.image');
    Route::get('/plots/{plot}/payments/create', [PlotPaymentController::class, 'create'])->name('plots.payments.create');
    Route::post('/plots/{plot}/payments', [PlotPaymentController::class, 'store'])->name('plots.payments.store');
    Route::delete('/plots/{plot}/payments/{payment}', [PlotPaymentController::class, 'destroy'])->name('plots.payments.destroy');
    Route::post('/plots/{plot}/documents', [PlotDocumentController::class, 'store'])->name('plots.documents.store');
    Route::delete('/plots/{plot}/documents/{document}', [PlotDocumentController::class, 'destroy'])->name('plots.documents.destroy');
    Route::resource('plots', PlotController::class);

    // Plot Share Bookings (customer buys plot shares)
    Route::get('/bookings/{booking}/payments/create', [PlotBookingPaymentController::class, 'create'])->name('bookings.payments.create');
    Route::post('/bookings/{booking}/payments', [PlotBookingPaymentController::class, 'store'])->name('bookings.payments.store');
    Route::delete('/bookings/{booking}/payments/{payment}', [PlotBookingPaymentController::class, 'destroy'])->name('bookings.payments.destroy');
    Route::get('/bookings/{booking}/expenses/create', [PlotBookingExpenseController::class, 'create'])->name('bookings.expenses.create');
    Route::post('/bookings/{booking}/expenses', [PlotBookingExpenseController::class, 'store'])->name('bookings.expenses.store');
    Route::delete('/bookings/{booking}/expenses/{expense}', [PlotBookingExpenseController::class, 'destroy'])->name('bookings.expenses.destroy');
    Route::post('/bookings/{booking}/documents', [PlotBookingDocumentController::class, 'store'])->name('bookings.documents.store');
    Route::delete('/bookings/{booking}/documents/{document}', [PlotBookingDocumentController::class, 'destroy'])->name('bookings.documents.destroy');
    Route::resource('bookings', PlotBookingController::class);

    // Loans
    Route::get('/loans/reports', [LoanReportController::class, 'index'])->name('loans.reports');
    Route::get('/loans/reports/{type}', [LoanReportController::class, 'show'])->name('loans.reports.show');
    Route::get('/loans/{loan}/repayments/create', [LoanRepaymentController::class, 'create'])->name('loans.repayments.create');
    Route::post('/loans/{loan}/repayments', [LoanRepaymentController::class, 'store'])->name('loans.repayments.store');
    Route::delete('/loans/{loan}/repayments/{repayment}', [LoanRepaymentController::class, 'destroy'])->name('loans.repayments.destroy');
    Route::resource('loans', LoanController::class);

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

/*
|--------------------------------------------------------------------------
| Public Customer Profile Completion (no auth — secure token in URL)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/customer-profile/{token}', [PublicCustomerProfileController::class, 'show'])
        ->name('customer-profile.show');
    Route::post('/customer-profile/{token}', [PublicCustomerProfileController::class, 'update'])
        ->name('customer-profile.update');
});
