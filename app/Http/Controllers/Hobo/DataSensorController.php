<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hobo\DataSensor;
use App\Models\Hobo\Loggers;

class DataSensorController extends Controller
{
    public function index(Request $request)
    {
        $data = Loggers::with('sensors.dataSensor')->get();
        return $data;
    }
}
