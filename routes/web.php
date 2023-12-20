<?php

use App\Http\Controllers\Hobo\HoboController;
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
