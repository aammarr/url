<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'App\Http\Controllers\PassportController@register');
Route::post('login', 'App\Http\Controllers\PassportController@login');
 
Route::middleware('auth:api')->group(function () {
    Route::get('user', 'App\Http\Controllers\PassportController@details');
 
    Route::resource('products', 'App\Http\Controllers\ProductController');
    Route::resource('urls', 'App\Http\Controllers\UrlController');
    Route::get('u/{code}', 'App\Http\Controllers\UrlController@userClick');
});