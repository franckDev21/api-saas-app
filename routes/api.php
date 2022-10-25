<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\OrderController;
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
        Route::post('products',[ProductController::class,'store']);
        Route::get('product/{product}',[ProductController::class,'show']);
        Route::post('product/{product}',[ProductController::class,'update']);
        Route::delete('product/{product}',[ProductController::class,'destroy']);
        Route::get('products/types',[ProductController::class,'getTypes']);
        Route::get('products/suppliers',[ProductController::class,'getSuppliers']);
        Route::post('products/add/{product}/input/supply',[ProductController::class,'addInput']);
        Route::post('products/add/{product}/output',[ProductController::class,'addOutput']);

        // orders
        Route::get('orders',[OrderController::class,'index']);
        Route::post('orders',[OrderController::class,'store']);
        Route::get('orders/{order}',[OrderController::class,'show']);
        Route::delete('orders/{order}',[OrderController::class,'destroy']);

        // histoty
        Route::get('history/all',[ProductController::class,'getAllHistory']);
        Route::get('history/procurement',[ProductController::class,'getProcurement']);

        // categories
        Route::get('categories',[CategoryController::class,'index']);

        // company
        Route::get('my/company',[CompanyController::class,'myCompany']);
        Route::post('my/company/picture/{company}',[CompanyController::class,'updatePictureCompany']);
        Route::post('my/company',[CompanyController::class,'store']);
        Route::post('my/company/{company}',[CompanyController::class,'update']);
        Route::post('user/toggle-active/{user}',[UserController::class,'toggleActiveUser']);

        // logout
        Route::post('auth/logout',[AuthController::class,'logout']);

        // users
        Route::get('users',[UserController::class,'getUsers']);
        Route::post('users/create',[UserController::class,'store']);

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
