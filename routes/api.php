<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WaitlistController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require __DIR__.'/v1/v1.php';

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Waitlist routes
Route::post('/waitlist', [WaitlistController::class, 'store']);
Route::get('/waitlist', [WaitlistController::class, 'index']);
