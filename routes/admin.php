<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('admin')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login_admin');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

    Route::middleware(['auth:sanctum', 'auth.role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'index');
            Route::get('/users/{id}', 'show');
            Route::post('/users/{id}/status', 'update_status');
        });

        Route::controller(QueryController::class)->group(function () {
            Route::get('/queries', 'index');
            Route::get('/queries/{query}', 'show');
        });
    });
});
