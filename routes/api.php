<?php

use Illuminate\Http\Request;

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

Route::group(['namespace' => 'API'], function () {
    Route::post('register', 'AuthController@register');
    Route::post('verify-account', 'AuthController@verifyAccount');
    Route::post('send-verify-mail', 'AuthController@sendVerifyMail');
    Route::post('login', 'AuthController@login');
    Route::post('send-mail-reset-password', 'AuthController@sendMailResetPassword');
    Route::post('reset-password', 'AuthController@resetPassword');
    //  Social login
    Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
    Route::get('/callback/{provider}', 'SocialController@callback');
    Route::get('users', 'UserController@index');
    Route::get('users/{id}', 'UserController@show');
    Route::delete('users/{id}', 'UserController@destroy');
    
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('logout', 'AuthController@logout');
        Route::post('update-password', 'AuthController@updatePassword');
        Route::get('profile/me', 'UserController@profile');
    });
});
