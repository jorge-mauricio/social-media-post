<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwitterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Twitter OAuth routes
Route::get('auth/twitter', [TwitterController::class, 'redirectToTwitter'])->name('twitter.login');
Route::get('auth/twitter/callback', [TwitterController::class, 'handleProviderCallback'])->name('twitter.callback');
Route::get('twitter/error', [TwitterController::class, 'showError'])->name('twitter.error');
