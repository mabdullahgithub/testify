<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'signUp']);
Route::post('/login', [AuthController::class, 'signIn']);
Route::post('/logout', [AuthController::class, 'signOut']);

//products routes
//apply middleware to protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [AuthController::class, 'index']);
    Route::post('/create-product', [AuthController::class, 'createProduct']);
    // Route::get('/products/{id}', [AuthController::class, 'show']);
    Route::post('/products/{id}', [AuthController::class, 'update']);
    Route::delete('/products/{id}', [AuthController::class, 'destroy']);
});
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
