<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetPlanController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
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

    // Projects & Kanban
    Route::resource('projects', ProjectController::class)->names([
        'index' => 'projects.index',
        'create' => 'projects.create',
        'store' => 'projects.store',
        'show' => 'projects.show',
        'edit' => 'projects.edit',
        'update' => 'projects.update',
        'destroy' => 'projects.destroy',
    ]);
    Route::get('projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');
    Route::post('projects/{project}/cards', [CardController::class, 'store'])->name('projects.cards.store');

    // Card operations for AJAX from board view (need board parameter to match API controller signature)
    Route::patch('projects/{project}/boards/{board}/cards/{card}/move', [\App\Http\Controllers\Api\CardController::class, 'move'])->name('projects.cards.move');
    Route::put('projects/{project}/boards/{board}/cards/{card}', [\App\Http\Controllers\Api\CardController::class, 'update'])->name('projects.cards.update');
    Route::delete('projects/{project}/boards/{board}/cards/{card}', [\App\Http\Controllers\Api\CardController::class, 'destroy'])->name('projects.cards.destroy');

    // Portfolio Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
