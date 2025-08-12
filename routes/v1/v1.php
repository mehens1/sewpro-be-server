<?php

use Lorisleiva\Actions\Facades\Actions;

Route::group(['prefix' => 'v1'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post("/register", \App\Actions\Auth\Register::class);
        Route::post("/login", \App\Actions\Auth\Login::class);
        Route::post("/reset-password", \App\Actions\Auth\ResetPassword::class);
        Route::post("/verify-code", \App\Actions\Auth\VerifyCode::class);
        Route::post("/set-new-password", \App\Actions\Auth\SetNewPassword::class);
    });

    Route::group(['middleware' => ['auth:api']], function () {

        Route::group(['prefix' => 'users'], function () {
            Route::get("/", \App\Actions\Users\ListUser::class);
            Route::post("/", \App\Actions\Users\AddUser::class);
        });

        Route::group(['prefix' => 'user'], function () {
            Route::post("/me", \App\Actions\User\UpdateProfile::class);
            Route::group(['prefix' => 'password'], function () {
                Route::post("/change", \App\Actions\User\ChangePassword::class);
            });
        });

        Route::group(['prefix' => 'customers'], function () {
            Route::get("/", \App\Actions\Customer\GetCustomers::class);
            Route::post("/", \App\Actions\Customer\CreateCustomer::class);
        });
    });
});
