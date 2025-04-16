<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgetPasswordController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::controller(ForgetPasswordController::class)->group(function () {
    Route::post('/forget-password', 'forgot');
    Route::post('/verify-code', 'verify');
    Route::post('/set-password', 'reset');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/get-profile', 'profile');
        Route::post('/edit-profile', 'edit_profile');
        Route::post('/change-password', 'change_password');
        // Route::post('/change-avatar', 'change_avatar');
        Route::get('/notifications/all/list', 'all_notifications');
        Route::get('/notifications/read/list', 'read_notifications');
        Route::get('/notifications/unread/list', 'unread_notifications');
        Route::get('/notifications/unread/count', 'unread_notifications_count');
        Route::post('/notifications/{id}', 'mark_notification');
        Route::post('/notifications/read', 'mark_all_as_read');
    });

    Route::controller(PageController::class)->group(function () {
        Route::post('/contact-us', 'contact_us');
        Route::get('/content', 'get_page');
    });
});
