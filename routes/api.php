<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ProductController;
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
    
    // Protected route
    Route::group(['middleware' => ['auth:sanctum']],function(){

        // users
        Route::get('users/companies',[UserController::class,'index']);
        Route::delete('users/companies/{user}',[UserController::class,'deleteUserCompany']);
        Route::post('users/companies/toggle-active/{user}',[UserController::class,'toggleActiveUserCompany']);

        // custumers
        Route::get('customers',[CustomerController::class,'index']);
        Route::post('customers',[CustomerController::class,'store']);
        Route::delete('customers/{customer}',[CustomerController::class,'destroy']);
        Route::get('customer/{customer}',[CustomerController::class,'show']);
        Route::post('customer/{customer}',[CustomerController::class,'update']);

        // products
        Route::get('products',[ProductController::class,'index']);

        // company
        Route::get('my/company',[CompanyController::class,'myCompany']);
        Route::post('my/company/picture/{company}',[CompanyController::class,'updatePictureCompany']);
        Route::post('my/company',[CompanyController::class,'store']);
        Route::post('my/company/{company}',[CompanyController::class,'update']);
        Route::post('user/toggle-active/{user}',[UserController::class,'toggleActiveUser']);

        Route::post('auth/logout',[AuthController::class,'logout']);

        // users
        Route::get('users',[UserController::class,'getUsers']);
        Route::post('/users/create',[UserController::class,'store']);

        // get user info
        Route::post('auth/user/info',[AuthController::class,'getUserInfo']);

        // update user information
        Route::post('auth/user',[UserController::class,'updateUserInfo']);
        // update password
        Route::post('auth/user/password',[AuthController::class,'updateUserPassword']);
        // update user picture
        Route::post('auth/user/picture',[UserController::class,'updateUserPicture']);
    });
});
