<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use Illuminate\Auth\Middleware\Authenticate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::get('/home',[ProductController::class,'index'])->name('home');;
Route::get('/products/{id}',[ProductController::class,'show'])->name('show');



Route::middleware('auth:sanctum')->prefix('products')->name("products.")->group(function(){
    Route::post('/add',[ProductController::class,'store'])->name('store');
    //put is not working
    Route::post('/{id}',[ProductController::class,'update'])->name('update');
    Route::delete('/{id}',[ProductController::class,'destroy'])->name('destroy');
    Route::get('/{id}/like',[ProductController::class,'like'])->name('like');
});

Route::middleware('guest')->group(function(){
    Route::post('/signup',[AuthController::class,'signup'])->name('signup');
    Route::post('/login',[AuthController::class,'login'])->name('login');
});

Route::middleware('auth:sanctum')->get('/logout',[AuthController::class,'logout'])->name('logout');
Route::middleware('auth:sanctum')->get('/profile',[AuthController::class,'profile'])->name('profile');