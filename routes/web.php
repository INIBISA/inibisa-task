<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [WorkspaceController::class, 'tasks'])->name('tasks');
    Route::post('/tasks', [WorkspaceController::class, 'storeTask'])->name('tasks.store');
    Route::put('/tasks/{task}', [WorkspaceController::class, 'updateTask'])->name('tasks.update');
    Route::post('/tasks/{task}/subtasks', [WorkspaceController::class, 'subtask'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}', [WorkspaceController::class, 'toggleSubtask'])->name('subtasks.toggle');
    Route::get('/products', [WorkspaceController::class, 'products'])->name('products');
    Route::post('/products', [WorkspaceController::class, 'storeProduct'])->name('products.store');
    Route::get('/ideas', [WorkspaceController::class, 'ideas'])->name('ideas');
    Route::post('/ideas', [WorkspaceController::class, 'storeIdea'])->name('ideas.store');
    Route::post('/ideas/{idea}/convert', [WorkspaceController::class, 'convertIdea'])->name('ideas.convert');
    Route::get('/audiences', [WorkspaceController::class, 'audiences'])->name('audiences');
    Route::post('/audiences', [WorkspaceController::class, 'storeAudience'])->name('audiences.store');
    Route::get('/team', [WorkspaceController::class, 'team'])->name('team');
    Route::post('/team', [WorkspaceController::class, 'storeMember'])->name('team.store');
    Route::put('/team/{user}', [WorkspaceController::class, 'updateMember'])->name('team.update');
    Route::patch('/team/{user}/deactivate', [WorkspaceController::class, 'deactivateMember'])->name('team.deactivate');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
