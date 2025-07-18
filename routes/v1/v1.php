<?php

use Lorisleiva\Actions\Facades\Actions;

Route::group(['prefix' => 'v1'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post("/register", \App\Actions\Auth\Register::class);
        Route::post("/login", \App\Actions\Auth\Login::class);
    });

    Route::group(['middleware' => ['auth:api'] ], function () {

        Route::group(['prefix' => 'users' ], function () {
            Route::get("/", \App\Actions\Users\ListUser::class);
            // Route::post("/", \App\Actions\Users\CreateUser::class);
            Route::post("/", \App\Actions\Users\AddUser::class);
        });

        Route::group(['prefix' => 'user' ], function () {

            Route::post("/me", \App\Actions\User\UpdateProfile::class);


            Route::group(['prefix' => 'password' ], function () {
                Route::post("/change", \App\Actions\User\ChangePassword::class);
            });

        });

    });
});
