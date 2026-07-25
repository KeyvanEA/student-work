<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SkillController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile' , [ProfileController::class, 'update'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/skills', [SkillController::class, 'index']);
    Route::put('/profile/skills', [SkillController::class, 'update']);
});
