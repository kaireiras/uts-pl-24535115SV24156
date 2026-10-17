<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingController;

/*
|--------------------------------------------------------------------------
| Public Routes (Dapat diakses oleh HomeView tanpa login)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::get('/portfolio-data', [SettingController::class, 'getPortfolioData']);
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib Header: Authorization: Bearer <token>)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin CRUD Project (Sesuai soal CPMK1: tambah, ubah, hapus)
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Admin Settings (Edit About & Upload me.jpg)
    Route::post('/settings/about', [SettingController::class, 'updateAbout']);
    Route::post('/settings/upload-photo', [SettingController::class, 'uploadPhoto']);
});