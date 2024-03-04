<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ScheduleController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/auth/facebook', 'SocialAuthController@facebookAuth');
Route::post('/auth/facebook', [SocialAuthController::class, 'facebookAuth']);
Route::get('/auth/facebook', [SocialAuthController::class, 'facebookAuth']);
Route::get('/auth/facebook/callback', [SocialAuthController::class, 'callback']);

Route::post('/post/create', 'PostController@create');
Route::post('/post/schedule', 'ScheduleController@schedule');
