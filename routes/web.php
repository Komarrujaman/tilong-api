<?php

use App\Http\Controllers\Hobo\DataSensorController;
use App\Http\Controllers\Hobo\HoboController;
use App\Http\Controllers\Wl\WLController;
use App\Http\Controllers\WL\WlLoggerController;
use App\Models\WL\WlLogger;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Hobo Endpoint
Route::get('loginHobo', [HoboController::class, 'login']);
Route::get('awsHobo', [HoboController::class, 'aws']);
Route::get('/fetch-and-save', [HoboController::class, 'fetchDataAndSave']);
Route::get('aws-db', [DataSensorController::class, 'index']);

Route::get('/awlrHobo', [WLController::class, 'awlr']);
Route::get('awlr', [WlLoggerController::class, 'index']);
Route::get('awlr/fetch-and-save', [WLController::class, 'fetchDataAndSave']);
