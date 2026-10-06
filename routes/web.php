<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\CommentAttachmentController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::post('/push-subscriptions/test', [PushSubscriptionController::class, 'test'])->name('push-subscriptions.test');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
    Route::get('/dashboard', [WorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [WorkspaceController::class, 'tasks'])->name('tasks');
    Route::post('/tasks', [WorkspaceController::class, 'storeTask'])->name('tasks.store');
    Route::put('/tasks/{task}', [WorkspaceController::class, 'updateTask'])->name('tasks.update');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::get('/comment-attachments/{attachment}', [CommentAttachmentController::class, 'show'])->name('comment-attachments.show');
    Route::delete('/tasks/{task}', [WorkspaceController::class, 'destroyTask'])->name('tasks.destroy');
    Route::post('/tasks/{task}/subtasks', [WorkspaceController::class, 'subtask'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}', [WorkspaceController::class, 'toggleSubtask'])->name('subtasks.toggle');
    Route::get('/products', [WorkspaceController::class, 'products'])->name('products');
    Route::post('/products', [WorkspaceController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [WorkspaceController::class, 'updateProduct'])->name('products.update');
    Route::patch('/products/{product}/archive', [WorkspaceController::class, 'archiveProduct'])->name('products.archive');
    Route::delete('/products/{product}', [WorkspaceController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/ideas', [WorkspaceController::class, 'ideas'])->name('ideas');
    Route::post('/ideas', [WorkspaceController::class, 'storeIdea'])->name('ideas.store');
    Route::put('/ideas/{idea}', [WorkspaceController::class, 'updateIdea'])->name('ideas.update');
    Route::delete('/ideas/{idea}', [WorkspaceController::class, 'destroyIdea'])->name('ideas.destroy');
    Route::post('/ideas/{idea}/convert', [WorkspaceController::class, 'convertIdea'])->name('ideas.convert');
    Route::get('/audiences', [WorkspaceController::class, 'audiences'])->name('audiences');
    Route::post('/audiences', [WorkspaceController::class, 'storeAudience'])->name('audiences.store');
    Route::put('/audiences/{audience}', [WorkspaceController::class, 'updateAudience'])->name('audiences.update');
    Route::patch('/audiences/{audience}/archive', [WorkspaceController::class, 'archiveAudience'])->name('audiences.archive');
    Route::delete('/audiences/{audience}', [WorkspaceController::class, 'destroyAudience'])->name('audiences.destroy');
    Route::get('/social-media-accounts', [WorkspaceController::class, 'socialMediaAccounts'])->name('social-media-accounts');
    Route::post('/social-media-accounts', [WorkspaceController::class, 'storeSocialMediaAccount'])->name('social-media-accounts.store');
    Route::put('/social-media-accounts/{socialMediaAccount}', [WorkspaceController::class, 'updateSocialMediaAccount'])->name('social-media-accounts.update');
    Route::patch('/social-media-accounts/{socialMediaAccount}/deactivate', [WorkspaceController::class, 'deactivateSocialMediaAccount'])->name('social-media-accounts.deactivate');
    Route::delete('/social-media-accounts/{socialMediaAccount}', [WorkspaceController::class, 'destroySocialMediaAccount'])->name('social-media-accounts.destroy');
    Route::get('/team', [WorkspaceController::class, 'team'])->name('team');
    Route::post('/team', [WorkspaceController::class, 'storeMember'])->name('team.store');
    Route::put('/team/{user}', [WorkspaceController::class, 'updateMember'])->name('team.update');
    Route::patch('/team/{user}/deactivate', [WorkspaceController::class, 'deactivateMember'])->name('team.deactivate');
    Route::delete('/team/{user}', [WorkspaceController::class, 'destroyMember'])->name('team.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
