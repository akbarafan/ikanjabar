<?php

use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('user.dashboard');
});

Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
