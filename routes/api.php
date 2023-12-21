<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Hobo\DataSensorController;
use App\Http\Controllers\Hobo\HoboController;
use App\Http\Controllers\Hobo\LoggerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Wl\WLController;
use App\Http\Controllers\WL\WlLoggerController;
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
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
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


// HOBO Endpoint
Route::post('loginHobo', [HoboController::class, 'login']);
Route::get('awsHobo', [HoboController::class, 'aws']);


// Device Management
// AWS Logger Management
Route::post('/logger/add', [LoggerController::class, 'store']);

// AWLR Logger management
Route::post('/awlr/logger/add', [WlLoggerController::class, 'store']);
Route::get('/awlr/logger/all', [WlLoggerController::class, 'index']);
Route::get('/awlrHobo', [WLController::class, 'awlr']);

// Data Management
// AWS
Route::get('aws-data', [DataSensorController::class, 'index']);
// WL
Route::get('awlr-data', [DataSensorController::class, 'waterLevel']);
