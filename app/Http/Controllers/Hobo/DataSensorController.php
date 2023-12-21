<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hobo\DataSensor;
use App\Models\Hobo\Loggers;
use App\Models\WL\WlLogger;

class DataSensorController extends Controller
{
    public function index(Request $request)
    {
        $data = Loggers::with('sensors.dataSensor')->get();
        return $data;
    }

    public function waterLevel(Request $request)
    {
        $data = WlLogger::with('sensors.dataSensor')->get();
        return $data;
    }
}
