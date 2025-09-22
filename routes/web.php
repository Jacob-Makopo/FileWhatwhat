<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\MunicipalityDeadlineController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Admin Controllers
use App\Http\Controllers\Admin\{
    UserController,
    RoleController,
    PermissionController,
    ReportController,
    AuditController
};
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Redirect root to dashboard (guests will get redirected to login)
Route::get('/', fn () => redirect()->route('dashboard'));

// ----------------
// Authentication routes
// ----------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard')
        ->middleware('permission:view dashboard');

    // Submissions
    Route::get('/submissions', [SubmissionController::class, 'index'])
        ->name('submissions.index')
        ->middleware('permission:view submissions');
    Route::post('/submissions', [SubmissionController::class, 'store'])
        ->name('submissions.store')
        ->middleware('permission:create submissions');

    // Uploads
    Route::get('/uploads', [UploadsController::class, 'index'])
        ->name('uploads.index')
        ->middleware('permission:view uploads');

    Route::post('/uploads', [UploadsController::class, 'store'])
        ->name('uploads.store')
        ->middleware('permission:create upload');

    Route::delete('/uploads/{upload}', [UploadsController::class, 'destroy'])
        ->name('uploads.destroy')
        ->middleware('permission:delete upload');

    Route::get('/uploads/history', [UploadsController::class, 'history'])
        ->name('uploads.history')
        ->middleware('permission:view uploads');

    Route::get('/uploads/export', [UploadsController::class, 'export'])
        ->name('uploads.export')
        ->middleware('permission:export uploads');

// API route for existing uploads check
    Route::get('/api/municipalities/{municipality}/existing-uploads', function ($municipalityId) {
        $existingCompanyIds = \App\Models\Uploads::where('municipality_id', $municipalityId)
            ->pluck('company_id')
            ->unique()
            ->toArray();

        return response()->json(['existing_company_ids' => $existingCompanyIds]);
    })->middleware('auth');

    // Deadlines
    Route::get('/deadlines/municipalities', [MunicipalityDeadlineController::class, 'index'])
        ->name('deadlines.municipalities.index')
        ->middleware('permission:view deadlines');
    Route::post('/deadlines/municipalities', [MunicipalityDeadlineController::class, 'store'])
        ->name('deadlines.municipalities.store')
        ->middleware('permission:create deadline');
    Route::put('/deadlines/municipalities/{deadline}', [MunicipalityDeadlineController::class, 'update'])
        ->name('deadlines.municipalities.update')
        ->middleware('permission:edit deadline');
    Route::delete('/deadlines/municipalities/{deadline}', [MunicipalityDeadlineController::class, 'destroy'])
        ->name('deadlines.municipalities.destroy')
        ->middleware('permission:delete deadline');
    Route::post('/deadlines/assignments', [MunicipalityDeadlineController::class, 'storeAssignment'])
        ->name('deadlines.assignments.store')
        ->middleware('permission:create deadline');
    Route::delete('/deadlines/assignments/{assignment}', [MunicipalityDeadlineController::class, 'destroyAssignment'])
        ->name('deadlines.assignments.destroy')
        ->middleware('permission:delete deadline');
    Route::post('/deadlines/create-with-assignments', [MunicipalityDeadlineController::class, 'createWithAssignments'])
        ->name('deadlines.create-with-assignments');

    // Combined deadline and assignment creation
    Route::post('/deadlines/create-with-assignments', [MunicipalityDeadlineController::class, 'createWithAssignments'])
        ->name('deadlines.create-with-assignments')
        ->middleware('permission:create deadline');

    // Admin
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        // Users
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:view users');
        Route::get('/users/{user}', [UserController::class, 'edit'])
            ->name('users.edit')
            ->middleware('permission:edit users');
        Route::put('/users/{user}', [UserController::class, 'update'])
            ->name('users.update')
            ->middleware('permission:edit users');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('permission:delete users');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('permission:manage roles');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('permission:manage roles');
        Route::post('/roles', [RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('permission:manage roles');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->name('roles.update')
            ->middleware('permission:manage roles');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('permission:manage roles');

        // Permissions
        Route::get('/permissions/data', [PermissionController::class, 'data'])
            ->name('permissions.data')
            ->middleware('permission:manage permissions');

        // Companies
        Route::get('/companies', [CompanyController::class, 'index'])
            ->name('companies.index')
            ->middleware('permission:view companies');

        // Municipalities
        Route::get('/municipalities', [MunicipalityController::class, 'index'])
            ->name('municipalities.index')
            ->middleware('permission:view municipalities');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index')
            ->middleware('permission:view reports');

        // Audits
        Route::get('/audits', [AuditController::class, 'index'])
            ->name('audits.index')
            ->middleware('permission:view audits');
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Fallback route
Route::fallback(fn () => Inertia::render('Errors/404', ['status' => 404]))->name('fallback');
