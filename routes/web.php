<?php

use App\Http\Controllers\Admin\BranchDetailController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchDetailController as MainBranchDetailController;
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

Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');

Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

// Fish Types Routes
Route::prefix('fish-types')->name('fish-types.')->group(function () {
    Route::get('/', [FishTypeController::class, 'index'])->name('index');
    Route::get('/data', [FishTypeController::class, 'getData'])->name('data');
    Route::post('/', [FishTypeController::class, 'store'])->name('store');
    Route::get('/{id}', [FishTypeController::class, 'show'])->name('show');
    Route::put('/{id}', [FishTypeController::class, 'update'])->name('update');
    Route::delete('/{id}', [FishTypeController::class, 'destroy'])->name('destroy');
});

// Ponds Routes
Route::get('/ponds', [PondController::class, 'index'])->name('ponds.index');
Route::post('/ponds', [PondController::class, 'store'])->name('ponds.store');
Route::get('/ponds/{id}', [PondController::class, 'show'])->name('ponds.show');
Route::put('/ponds/{id}', [PondController::class, 'update'])->name('ponds.update');
Route::delete('/ponds/{id}', [PondController::class, 'destroy'])->name('ponds.destroy');

// Fish Batches Routes
Route::get('/fish-batches', [FishBatchController::class, 'index'])->name('fish-batches.index');
Route::post('/fish-batches', [FishBatchController::class, 'store'])->name('fish-batches.store');
Route::get('/fish-batches/{id}', [FishBatchController::class, 'show'])->name('fish-batches.show');
Route::put('/fish-batches/{id}', [FishBatchController::class, 'update'])->name('fish-batches.update');
Route::delete('/fish-batches/{id}', [FishBatchController::class, 'destroy'])->name('fish-batches.destroy');

// Water Quality Routes
Route::get('/water-qualities', [WaterQualityController::class, 'index'])->name('water-qualities.index');
Route::post('/water-qualities', [WaterQualityController::class, 'store'])->name('water-qualities.store');
Route::get('/water-qualities/{id}', [WaterQualityController::class, 'show'])->name('water-qualities.show');
Route::put('/water-qualities/{id}', [WaterQualityController::class, 'update'])->name('water-qualities.update');
Route::delete('/water-qualities/{id}', [WaterQualityController::class, 'destroy'])->name('water-qualities.destroy');

// Fish Growth Routes
Route::get('/fish-growth', [FishGrowthLogController::class, 'index'])->name('fish-growth.index');
Route::post('/fish-growth', [FishGrowthLogController::class, 'store'])->name('fish-growth.store');
Route::get('/fish-growth/{id}', [FishGrowthLogController::class, 'show'])->name('fish-growth.show');
Route::put('/fish-growth/{id}', [FishGrowthLogController::class, 'update'])->name('fish-growth.update');
Route::delete('/fish-growth/{id}', [FishGrowthLogController::class, 'destroy'])->name('fish-growth.destroy');

// Feeding Routes
Route::get('/feedings', [FeedingController::class, 'index'])->name('feedings.index');
Route::post('/feedings', [FeedingController::class, 'store'])->name('feedings.store');
Route::get('/feedings/{id}', [FeedingController::class, 'show'])->name('feedings.show');
Route::put('/feedings/{id}', [FeedingController::class, 'update'])->name('feedings.update');
Route::delete('/feedings/{id}', [FeedingController::class, 'destroy'])->name('feedings.destroy');

// Mortality Routes
Route::get('/mortalities', [MortalityController::class, 'index'])->name('mortalities.index');
Route::post('/mortalities', [MortalityController::class, 'store'])->name('mortalities.store');
Route::get('/mortalities/{id}', [MortalityController::class, 'show'])->name('mortalities.show');
Route::put('/mortalities/{id}', [MortalityController::class, 'update'])->name('mortalities.update');
Route::delete('/mortalities/{id}', [MortalityController::class, 'destroy'])->name('mortalities.destroy');

// Sales Routes
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{id}', [SaleController::class, 'show'])->name('sales.show');
Route::put('/sales/{id}', [SaleController::class, 'update'])->name('sales.update');
Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');

// Fish Transfer Routes
Route::get('/fish-transfers', [FishBatchTransferController::class, 'index'])->name('fish-transfers.index');
Route::post('/fish-transfers', [FishBatchTransferController::class, 'store'])->name('fish-transfers.store');
Route::get('/fish-transfers/{id}', [FishBatchTransferController::class, 'show'])->name('fish-transfers.show');
Route::put('/fish-transfers/{id}', [FishBatchTransferController::class, 'update'])->name('fish-transfers.update');
Route::delete('/fish-transfers/{id}', [FishBatchTransferController::class, 'destroy'])->name('fish-transfers.destroy');


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    
    // Branch routes with search functionality
    Route::resource('branches', BranchController::class);
    Route::post('branches/search', [BranchController::class, 'search'])->name('branches.search');
    
    Route::resource('ponds', PondController::class);
    
    // Branch detail route - tambahkan ini
    Route::get('branches/{branch}/detail', [MainBranchDetailController::class, 'show'])->name('branches.detail');

    Route::prefix('fish-batches')->name('fish-batches.')->group(function () {
        Route::get('/', [FishBatchController::class, 'index'])->name('index');
        Route::get('/create', [FishBatchController::class, 'create'])->name('create');
        Route::post('/', [FishBatchController::class, 'store'])->name('store');
        Route::get('/{fishBatch}', [FishBatchController::class, 'show'])->name('show');
        Route::get('/{fishBatch}/edit', [FishBatchController::class, 'edit'])->name('edit');
        Route::put('/{fishBatch}', [FishBatchController::class, 'update'])->name('update');
        Route::delete('/{fishBatch}', [FishBatchController::class, 'destroy'])->name('destroy');

        Route::get('/{fishBatch}/transfer', [FishBatchController::class, 'transferForm'])->name('transfer.form');
        Route::post('/{fishBatch}/transfer', [FishBatchController::class, 'transfer'])->name('transfer');
    });

    Route::resource('water-quality', WaterQualityController::class);
    Route::get('/water-quality/pond/{pond}', [WaterQualityController::class, 'forPond'])->name('water-quality.pond');

    Route::resource('fish-growth', FishGrowthController::class);
    Route::get('/fish-growth/batch/{fishBatch}', [FishGrowthController::class, 'forBatch'])->name('fish-growth.batch');

    Route::resource('feeding', FeedingController::class);
    Route::get('/feeding/batch/{fishBatch}', [FeedingController::class, 'forBatch'])->name('feeding.batch');

    Route::resource('mortality', MortalityController::class);
    Route::get('/mortality/batch/{fishBatch}', [MortalityController::class, 'forBatch'])->name('mortality.batch');

    Route::resource('sales', SalesController ::class);
    Route::get('/sales/batch/{fishBatch}', [SalesController::class, 'forBatch'])->name('sales.batch');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/production', [ReportController::class, 'production'])->name('production');
        Route::get('/production/export', [ReportController::class, 'exportProduction'])->name('production.export');

        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/financial/export', [ReportController::class, 'exportFinancial'])->name('financial.export');

        Route::get('/water-quality', [ReportController::class, 'waterQuality'])->name('water-quality');
        Route::get('/water-quality/export', [ReportController::class, 'exportWaterQuality'])->name('water-quality.export');
    });
});

Route::get('/settings', function () {
    return view('admin.settings.index');
})->name('settings');

Route::get('/profile', function () {
    return view('admin.profile.index');
})->name('profile');

Route::get('/notifications', function () {
    return view('admin.notifications.index');
})->name('notifications');

Route::get('/help', function () {
    return view('admin.help.index');
})->name('help');
