<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ROUTE
Route::group(['prefix' => 'v1'],function(){
    // auth
    Route::post('auth/register',[AuthController::class,'register']);
    Route::post('auth/login',[AuthController::class,'login']);
    // Public route
    Route::apiResource('companies',CompanyController::class);
    // Protected route
    Route::group(['middleware' => ['auth:sanctum']],function(){
        Route::post('auth/logout',[AuthController::class,'logout']);
        // users
        Route::apiResource('users',UserController::class);
        // get user info
        Route::post('auth/user/info',[AuthController::class,'getUserInfo']);
    });
});
