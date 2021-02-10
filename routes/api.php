<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'getProfile']);
    Route::put('/me', [UserController::class, 'updateProfile']);
    Route::put('/newsletter', [UserController::class, 'updateNewsletterSubscription']);
});

Route::get('test', function (Request $request) {
    \App\Models\User::where('email')->get(['email']);
    return \App\Models\User::all(['id', 'name']);
});
