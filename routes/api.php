<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PicController;

// Auth 

Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function (){

        Route::post('register', 'register');
        Route::post('login'   , 'login');
        Route::get ('profile' , 'profile')->middleware('auth.devpic');
        Route::post('logout'  , 'logout')->middleware('auth.devpic');

});


Route::middleware('auth.devpic')->post('auth/profile/edit' , [UserController::class , 'editProfile']);

Route::prefix('pics')
    ->middleware('auth.devpic')
    ->controller(PicController::class)->group(function(){

        Route::post('create' , 'store');
        Route::post('search' , 'search');
        Route::post('{id}/delete' , 'destroy');
        Route::post('{id}/update' , 'update');
        Route::post('{id}/like' , 'like');
        Route::post('{id}/comment' , 'comment');


});



Route::prefix('users/user')
    ->middleware('auth.devpic')
    ->controller(UserController::class)->group(function(){

        Route::post('/{id}/follow' , 'follow');
        Route::get('/{slug}' , 'findUser');
       
});

