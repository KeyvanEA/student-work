<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/complaints',[AdminComplaintController::class, 'index']);
    Route::get('/admin/complaints/{complaint}',[AdminComplaintController::class, 'show']);
    Route::patch('/admin/complaints/{complaint}/review',[AdminComplaintController::class, 'review']);
    Route::patch('/admin/complaints/{complaint}/resolve',[AdminComplaintController::class, 'resolve']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard',[UserDashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile' , [ProfileController::class, 'update'])->middleware('auth:sanctum');

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/skills', [SkillController::class, 'index']);
    Route::put('/profile/skills', [SkillController::class, 'update']);
    Route::get('/profile/satisfaction' , [ReviewController::class, 'satisfaction']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/tasks/{task}/cancel', [TaskController::class, 'cancel']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('tasks/mine',  [TaskController::class, 'mine']);
    Route::post('/tasks/{task}/applications', [ApplicationController::class, 'store']);
    Route::get('/tasks/{task}/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::patch('/applications/{application}/accept', [ApplicationController::class, 'accept']);
    Route::patch('/applications/{application}/reject', [ApplicationController::class, 'reject']);
    Route::get('/applications', [ApplicationController::class, 'indexAll']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects/{project}/deliveries', [DeliveryController::class, 'store']);
    Route::get('/projects/{project}/deliveries', [DeliveryController::class, 'index']);
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);
    Route::get('/deliveries/{delivery}/files/{file}/preview', [DeliveryController::class, 'preview'])->name('deliveries.files.preview');
    Route::get('/deliveries/{delivery}/files/{file}/download', [DeliveryController::class, 'download'])->name('deliveries.files.download');
    Route::patch('/deliveries/{delivery}/reject', [DeliveryController::class, 'reject']);
    Route::patch('/deliveries/{delivery}/accept', [DeliveryController::class, 'accept']);
    Route::patch('/projects/{project}/payment', [ProjectController::class, 'payment']);
    Route::post('/projects/{project}/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::get('/complaints/related', [ComplaintController::class, 'related']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::post('/projects/{project}/reviews', [ReviewController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);

});
Route::get('/tasks', [TaskController::class, 'index']);
Route::get('/tasks/{id}', [TaskController::class, 'show']);

