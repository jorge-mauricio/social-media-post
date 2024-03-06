<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\SocialAuthController;
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

// Route::post('/auth/facebook', 'SocialAuthController@facebookAuth');
// Route::get('/auth/facebook/callback', 'SocialAuthController@callback');

// Route::get('auth/twitter', 'TwitterController@redirectToProvider')->name('twitter.login');
// Route::get('auth/twitter/callback', 'TwitterController@handleProviderCallback')->name('twitter.callback');


// Route::get('auth/twitter', [TwitterController::class, 'redirectToProvider'])->name('twitter.login');
Route::get('auth/twitter', [TwitterController::class, 'redirectToTwitter'])->name('twitter.login');
Route::get('auth/twitter/callback', [TwitterController::class, 'handleProviderCallback'])->name('twitter.callback');
Route::get('auth/twitter/post', [TwitterController::class, 'postTweet'])->name('twitter.post');

Route::get('/twitter/error', [TwitterController::class, 'showError'])->name('twitter.error');
