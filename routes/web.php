<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

Route::get('/', function () {
    return Redirect::away('https://sewpro.app');
});

Route::view('/privacy-policy', 'privacy-policy');
// Route::get('/privacy-policy', function () {
//     return View('privacy-policy');
// });
