<?php

use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Projects
    Route::apiResource('projects', ProjectController::class)->names([
        'index' => 'api.projects.index',
        'store' => 'api.projects.store',
        'show' => 'api.projects.show',
        'update' => 'api.projects.update',
        'destroy' => 'api.projects.destroy',
    ]);

    // Boards
    Route::get('/projects/{project}/boards/{board}', [BoardController::class, 'show']);

    // Cards
    Route::prefix('projects/{project}/boards/{board}')->group(function () {
        Route::get('/cards', [CardController::class, 'index']);
        Route::post('/cards', [CardController::class, 'store']);
        Route::get('/cards/{card}', [CardController::class, 'show']);
        Route::put('/cards/{card}', [CardController::class, 'update']);
        Route::delete('/cards/{card}', [CardController::class, 'destroy']);
        Route::patch('/cards/{card}/move', [CardController::class, 'move']);
    });

    // Checklists
    Route::apiResource('checklists', ChecklistController::class)->only(['store', 'destroy']);

    // Checklist Items
    Route::apiResource('checklist-items', ChecklistItemController::class)->only(['store', 'update', 'destroy']);
    Route::post('/checklist-items/{checklistItem}/toggle', [ChecklistItemController::class, 'toggle']);

    // Milestones
    Route::post('/milestones/{milestone}/toggle', function (\App\Models\Milestone $milestone, Request $request) {
        $milestone->update([
            'is_completed' => $request->boolean('is_completed')
        ]);
        return response()->json(['success' => true, 'milestone' => $milestone]);
    });
});
