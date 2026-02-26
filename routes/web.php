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
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\MyTaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectExpenseController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\ProjectRecapController;
use App\Http\Controllers\ProjectTermController;
use App\Http\Controllers\ProjectVendorController;
use App\Http\Controllers\TaxMasterController;
use App\Http\Controllers\TaskProjectController;
use App\Http\Controllers\TaskProjectKanbanController;
use App\Http\Controllers\TaskProjectTaskController;
use App\Http\Controllers\UserController;
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
    Route::get('budget-plans/{budget_plan}/realizations/{expense}/invoice-proof', [BudgetPlanRealizationController::class, 'downloadInvoiceProof'])
        ->name('budget-plans.realizations.invoice-proof');
    Route::get('budget-plans/{budget_plan}/realizations/{expense}/bank-mutation', [BudgetPlanRealizationController::class, 'downloadBankMutation'])
        ->name('budget-plans.realizations.bank-mutation');

    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    Route::get('tasks/my', [MyTaskController::class, 'my'])->name('tasks.my');
    Route::get('tasks/overdue', [MyTaskController::class, 'overdue'])->name('tasks.overdue');

    Route::get('projects/{project}/kanban', [KanbanController::class, 'show'])->name('projects.kanban');
    Route::patch('tasks/{task}/move', [KanbanController::class, 'move'])->name('tasks.move');

    Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::get('projects/{project}/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('projects/{project}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
    Route::put('projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
    Route::patch('projects/{project}/tasks/{task}/status', [TaskController::class, 'patchStatus'])->name('projects.tasks.status');
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

    Route::resource('users', UserController::class);
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');


    Route::resource('task-projects', TaskProjectController::class);

    Route::get('task-projects/{taskProject}/kanban', [TaskProjectKanbanController::class, 'show'])
        ->name('task-projects.kanban');
    Route::patch('task-projects/{taskProject}/tasks/{task}/move', [TaskProjectKanbanController::class, 'move'])
        ->name('task-projects.tasks.move');

    Route::get('task-projects/{taskProject}/tasks', [TaskProjectTaskController::class, 'index'])
        ->name('task-projects.tasks.index');
    Route::get('task-projects/{taskProject}/tasks/create', [TaskProjectTaskController::class, 'create'])
        ->name('task-projects.tasks.create');
    Route::post('task-projects/{taskProject}/tasks', [TaskProjectTaskController::class, 'store'])
        ->name('task-projects.tasks.store');
    Route::get('task-projects/{taskProject}/tasks/{task}/edit', [TaskProjectTaskController::class, 'edit'])
        ->name('task-projects.tasks.edit');
    Route::put('task-projects/{taskProject}/tasks/{task}', [TaskProjectTaskController::class, 'update'])
        ->name('task-projects.tasks.update');
    Route::delete('task-projects/{taskProject}/tasks/{task}', [TaskProjectTaskController::class, 'destroy'])
        ->name('task-projects.tasks.destroy');
});

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');
