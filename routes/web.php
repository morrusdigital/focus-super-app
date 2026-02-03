<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::resource('budget-plans', BudgetPlanController::class);
    Route::post('budget-plans/{budget_plan}/submit', [BudgetPlanController::class, 'submit'])
        ->name('budget-plans.submit');
    Route::post('budget-plans/{budget_plan}/approve', [BudgetPlanController::class, 'approve'])
        ->name('budget-plans.approve');
    Route::post('budget-plans/{budget_plan}/reject', [BudgetPlanController::class, 'reject'])
        ->name('budget-plans.reject');
    Route::post('budget-plans/{budget_plan}/request-revision', [BudgetPlanController::class, 'requestRevision'])
        ->name('budget-plans.request-revision');
});

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');
