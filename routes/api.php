<?php

use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Company\Controllers\CompanyController;
use App\Domains\Company\Controllers\MembershipController;
use App\Domains\Customer\Controllers\CustomerController;
use App\Domains\Document\Controllers\DocumentController;
use App\Domains\Document\Controllers\DocumentVersionController;
use App\Domains\Document\Controllers\FolderController;
use App\Domains\Employee\Controllers\EmployeeController;
use App\Domains\Project\Controllers\InvestorController;
use App\Domains\Project\Controllers\ProjectController;
use App\Domains\Property\Controllers\BuildingController;
use App\Domains\Property\Controllers\FloorController;
use App\Domains\Property\Controllers\UnitController;
use App\Domains\Property\Controllers\UnitTypeController;
use App\Domains\Shareholder\Controllers\ShareholderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'company'])->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Companies (listing & creation don't require company context)
    Route::apiResource('companies', CompanyController::class);

    // Company-scoped routes
    Route::prefix('companies/{company}')->middleware('company.required')->group(function () {

        // Memberships
        Route::apiResource('members', MembershipController::class)
            ->parameters(['members' => 'membership']);

        // Shareholders
        Route::apiResource('shareholders', ShareholderController::class);

        // Projects
        Route::apiResource('projects', ProjectController::class);

        // Project Investors
        Route::apiResource('projects/{project}/investors', InvestorController::class)
            ->parameters(['investors' => 'investor']);

        // Employees
        Route::apiResource('employees', EmployeeController::class);

        // Customers
        Route::apiResource('customers', CustomerController::class);

        // Documents
        Route::apiResource('documents', DocumentController::class);
        Route::post('documents/{document}/attach', [DocumentController::class, 'attachToEntity']);
        Route::get('documents/{document}/versions', [DocumentVersionController::class, 'index']);
        Route::post('documents/{document}/versions', [DocumentVersionController::class, 'store']);

        // Folders
        Route::apiResource('folders', FolderController::class)->except(['update']);

        // ─── Property Management ─────────────────────────────────────

        // Unit Types (company-scoped)
        Route::apiResource('unit-types', UnitTypeController::class)
            ->parameters(['unit-types' => 'unitType']);

        // Buildings (project-scoped)
        Route::apiResource('projects/{project}/buildings', BuildingController::class)
            ->parameters(['buildings' => 'building']);

        // Floors (building-scoped)
        Route::apiResource('projects/{project}/buildings/{building}/floors', FloorController::class)
            ->parameters(['floors' => 'floor']);

        // Units - list/show under project, create under floor
        Route::get('projects/{project}/units', [UnitController::class, 'index']);
        Route::get('projects/{project}/units/{unit}', [UnitController::class, 'show']);
        Route::post('projects/{project}/floors/{floor}/units', [UnitController::class, 'store']);
        Route::put('projects/{project}/units/{unit}', [UnitController::class, 'update']);
        Route::delete('projects/{project}/units/{unit}', [UnitController::class, 'destroy']);
    });
});
