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
use App\Http\Controllers\MortalityController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('user.dashboard');
});

Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

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
