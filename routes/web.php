<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminBranchController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PondController;
use App\Http\Controllers\FishBatchController;
use App\Http\Controllers\WaterQualityController;
use App\Http\Controllers\FishGrowthController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\FishBatchTransferController;
use App\Http\Controllers\FishGrowthLogController;
use App\Http\Controllers\FishTypeController;
use App\Http\Controllers\MortalityController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Guest Only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Default dashboard redirect
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('branches')) {
            return redirect()->route('user.dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('user.dashboard');
        }

        return redirect()->route('login')->with('error', 'Role tidak ditemukan.');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Branch Management
    Route::resource('branches', AdminBranchController::class);

    // User Management
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-verification', [AdminUserController::class, 'toggleVerification'])->name('users.toggle-verification');
});

/*
|--------------------------------------------------------------------------
| Branch Manager Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Fish Types Management
    Route::prefix('fish-types')->name('fish-types.')->group(function () {
        Route::get('/', [FishTypeController::class, 'index'])->name('index');
        Route::get('/data', [FishTypeController::class, 'getData'])->name('data');
        Route::post('/', [FishTypeController::class, 'store'])->name('store');
        Route::get('/{id}', [FishTypeController::class, 'show'])->name('show');
        Route::put('/{id}', [FishTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [FishTypeController::class, 'destroy'])->name('destroy');
    });

    // Ponds Management
    Route::resource('ponds', PondController::class);

    // Fish Batches Management
    Route::resource('fish-batches', FishBatchController::class);

    // Water Quality Management
    Route::resource('water-qualities', WaterQualityController::class);

    // Fish Growth Management
    Route::resource('fish-growth', FishGrowthLogController::class);

    // Feeding Management
    Route::resource('feedings', FeedingController::class);

    // Mortality Management
    Route::resource('mortalities', MortalityController::class);

    // Sales Management
    Route::resource('sales', SaleController::class);

    // Fish Transfer Management
    Route::resource('fish-transfers', FishBatchTransferController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
// Route::middleware(['auth'])->group(function () {
//     Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

//     // Read-only access to data
//     Route::get('/fish-types', [FishTypeController::class, 'index'])->name('fish-types.index');
//     Route::get('/ponds', [PondController::class, 'index'])->name('ponds.index');
//     Route::get('/fish-batches', [FishBatchController::class, 'index'])->name('fish-batches.index');
//     Route::get('/water-qualities', [WaterQualityController::class, 'index'])->name('water-qualities.index');
//     Route::get('/fish-growth', [FishGrowthLogController::class, 'index'])->name('fish-growth.index');
//     Route::get('/feedings', [FeedingController::class, 'index'])->name('feedings.index');
//     Route::get('/mortalities', [MortalityController::class, 'index'])->name('mortalities.index');
//     Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
//     Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
// });
