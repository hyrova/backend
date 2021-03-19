<?php

use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Routes protected for Admin use (mostly Back-Office)
|
*/

// Group with common name in the same file (better autocompletion for Laravel Idea)
Route::name('admin.')->group(function () {
    Route::patch('users/{id}', [AdminUserController::class, 'restore']);
    Route::apiResources([
        'users' => AdminUserController::class
    ]);
});
