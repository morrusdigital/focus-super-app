<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetPlanCategoryController;
use App\Http\Controllers\BudgetPlanController;
use App\Http\Controllers\BudgetPlanRealizationController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\CompanyBankAccountController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectReceiptController;
use App\Http\Controllers\ProjectExpenseController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\ProjectRecapController;
use App\Http\Controllers\ProjectTermController;
use App\Http\Controllers\ProjectVendorController;
use App\Http\Controllers\TaxMasterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('budget-plans/pdf', [BudgetPlanController::class, 'exportPdfIndex'])
        ->name('budget-plans.pdf');
    Route::get('budget-plans/{budget_plan}/pdf', [BudgetPlanController::class, 'exportPdf'])
        ->name('budget-plans.pdf.show');
    Route::resource('budget-plans', BudgetPlanController::class);
    Route::post('budget-plans/{budget_plan}/submit', [BudgetPlanController::class, 'submit'])
        ->name('budget-plans.submit');
    Route::post('budget-plans/{budget_plan}/approve', [BudgetPlanController::class, 'approve'])
        ->name('budget-plans.approve');
    Route::post('budget-plans/{budget_plan}/reject', [BudgetPlanController::class, 'reject'])
        ->name('budget-plans.reject');
    Route::post('budget-plans/{budget_plan}/request-revision', [BudgetPlanController::class, 'requestRevision'])
        ->name('budget-plans.request-revision');
    Route::get('budget-plans/{budget_plan}/realizations', [BudgetPlanRealizationController::class, 'index'])
        ->name('budget-plans.realizations.index');
    Route::post('budget-plans/{budget_plan}/realizations', [BudgetPlanRealizationController::class, 'store'])
        ->name('budget-plans.realizations.store');
    Route::put('budget-plans/{budget_plan}/realizations/{expense}', [BudgetPlanRealizationController::class, 'update'])
        ->name('budget-plans.realizations.update');
    Route::delete('budget-plans/{budget_plan}/realizations/{expense}', [BudgetPlanRealizationController::class, 'destroy'])
        ->name('budget-plans.realizations.destroy');

    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
    Route::get('project-recaps', [ProjectRecapController::class, 'index'])->name('project-recaps.index');
    Route::get('projects/{project}/terms', [ProjectTermController::class, 'index'])->name('projects.terms.index');
    Route::post('projects/{project}/terms', [ProjectTermController::class, 'store'])->name('projects.terms.store');
    Route::put('projects/{project}/terms/{term}', [ProjectTermController::class, 'update'])->name('projects.terms.update');
    Route::delete('projects/{project}/terms/{term}', [ProjectTermController::class, 'destroy'])->name('projects.terms.destroy');
    Route::post('projects/{project}/terms/{term}/mark-sent', [ProjectTermController::class, 'markSent'])->name('project-terms.mark-sent');

    Route::get('projects/{project}/vendors', [ProjectVendorController::class, 'index'])->name('projects.vendors.index');
    Route::post('projects/{project}/vendors', [ProjectVendorController::class, 'store'])->name('projects.vendors.store');
    Route::put('projects/{project}/vendors/{vendor}', [ProjectVendorController::class, 'update'])->name('projects.vendors.update');
    Route::delete('projects/{project}/vendors/{vendor}', [ProjectVendorController::class, 'destroy'])->name('projects.vendors.destroy');

    Route::get('projects/{project}/expenses', [ProjectExpenseController::class, 'index'])->name('projects.expenses.index');
    Route::post('projects/{project}/expenses', [ProjectExpenseController::class, 'store'])->name('projects.expenses.store');
    Route::put('projects/{project}/expenses/{expense}', [ProjectExpenseController::class, 'update'])->name('projects.expenses.update');
    Route::delete('projects/{project}/expenses/{expense}', [ProjectExpenseController::class, 'destroy'])->name('projects.expenses.destroy');

    Route::get('projects/{project}/progresses', [ProjectProgressController::class, 'index'])->name('projects.progresses.index');
    Route::post('projects/{project}/progresses', [ProjectProgressController::class, 'store'])->name('projects.progresses.store');

    Route::get('projects/{project}/receipts', [ProjectReceiptController::class, 'index'])->name('projects.receipts.index');
    Route::post('projects/{project}/receipts', [ProjectReceiptController::class, 'store'])->name('projects.receipts.store');
    Route::delete('projects/{project}/receipts/{receipt}', [ProjectReceiptController::class, 'destroy'])->name('projects.receipts.destroy');

    Route::resource('bank-accounts', CompanyBankAccountController::class);
    Route::resource('budget-plan-categories', BudgetPlanCategoryController::class);
    Route::resource('chart-accounts', ChartAccountController::class);
    Route::resource('tax-masters', TaxMasterController::class);
});

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');
