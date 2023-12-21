<?php

namespace App\Http\Controllers\WL;

use App\Http\Controllers\Controller;
use App\Models\WL\WlLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;

class WlLoggerController extends Controller
{

    public function index()
    {
        $wl_logger = WlLogger::allLogger();
        return $wl_logger;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sn' => [
                'required',
                ValidationRule::unique('loggers', 'sn')
            ],
            'nama' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ]);

        $input =  $request->all();

        if (WlLogger::where('sn', $input['sn'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Logger Sudah Ada',
            ]);
        }

        $logger = WlLogger::create($input);
        $success['sn'] = $logger->sn;
        $success['nama'] = $logger->nama;
        $success['lat'] = $logger->lat;
        $success['lng'] = $logger->lng;

        return response()->json([
            'success' => true,
            'message' => 'Tambah Logger Sukses',
            'data' => $success
        ]);
    }
}
