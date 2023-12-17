<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// User Management
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('all', [AuthController::class, 'allUser']);
    Route::get('user', [AuthController::class, 'show']);
    Route::post('edit', [AuthController::class, 'update']);
    Route::delete('delete/{id}', [AuthController::class, 'destroy']);
});
// Role Management
Route::middleware('auth:sanctum')->group(function () {
    Route::get('role', [RoleController::class, 'index']);
    Route::post('/role/add', [RoleController::class, 'store']);
});