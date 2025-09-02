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

            Route::group(['prefix' => 'company'], function () {
                Route::get("/", \App\Actions\Company\GetUserCompany::class);
                Route::post("/", \App\Actions\Company\CreateUserCompany::class);
            });
        });

        Route::group(['prefix' => 'customers'], function () {
            Route::get("/", \App\Actions\Customer\GetCustomers::class);
            Route::post("/", \App\Actions\Customer\CreateCustomer::class);
            Route::get("/{id}", \App\Actions\Customer\GetCustomer::class);
            Route::delete("/{id}", \App\Actions\Customer\DeleteCustomer::class);

            Route::group(['prefix' => 'measurements'], function () {
                Route::get('/{id}', \App\Actions\Measurements\GetMeasurement::class);
                Route::post('/', \App\Actions\Measurements\SaveMeasurement::class);
                Route::delete('/{cloth_type_id}', \App\Actions\Measurements\DeleteMeasurement::class);
            });
        });

        Route::group(['prefix' => 'invoices'], function () {
            Route::get('/{id}', \App\Actions\Invoices\ShowInvoice::class);
            Route::put('/customer/status', \App\Actions\Invoices\UpdateInvoiceStatus::class);
            Route::get('/customer/{customer_id}', \App\Actions\Invoices\ListCustomerInvoices::class);
            Route::post('/customer/generate', \App\Actions\Invoices\GenerateCustomerInvoice::class);
        });
    });
});
