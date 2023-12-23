<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hobo\DataSensor;
use App\Models\Hobo\Loggers;
use App\Models\WL\WlLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function awsLast()
    {
        $data = Loggers::with(['sensors' => function ($query) {
            $query->join(DB::raw('(SELECT sensor_id, MAX(timestamp) as latest_timestamp FROM data_sensors GROUP BY sensor_id) as latest_data'), function ($join) {
                $join->on('sensors.id', '=', 'latest_data.sensor_id');
            })
                ->leftJoin('data_sensors', function ($join) {
                    $join->on('sensors.id', '=', 'data_sensors.sensor_id')
                        ->on('data_sensors.timestamp', '=', 'latest_data.latest_timestamp');
                })
                ->select('sensors.*', 'data_sensors.*');
        }])
            ->orderBy('id', 'asc')
            ->get();

        return $data;
    }

    public function awlrLast()
    {
        $data = WlLogger::with(['sensors' => function ($query) {
            $query->join(DB::raw('(SELECT sensor_id, MAX(timestamp) as latest_timestamp FROM wl_data_sensors GROUP BY sensor_id) as latest_data'), function ($join) {
                $join->on('wl_sensors.id', '=', 'latest_data.sensor_id');
            })
                ->leftJoin('wl_data_sensors', function ($join) {
                    $join->on('wl_sensors.id', '=', 'wl_data_sensors.sensor_id')
                        ->on('wl_data_sensors.timestamp', '=', 'latest_data.latest_timestamp');
                })
                ->select('wl_sensors.*', 'wl_data_sensors.*');
        }])
            ->orderBy('id', 'asc')
            ->get();

        return $data;
    }

    public function awsDetail(Request $request, $sn)
    {
        try {
            // Validasi input, pastikan path parameter sn logger diisi
            if (empty($sn)) {
                throw new \Exception('Nomor Seri Logger harus diisi');
            }

            // Ambil data berdasarkan SN logger yang diinput pengguna
            $data = Loggers::with(['sensors.dataSensor' => function ($query) {
                // Menambahkan kondisi untuk memilih data hanya dalam dua hari terakhir dari timestamp terakhir
                $query->where('timestamp', '>', Carbon::now()->subDays(1)->toDateTimeString());
                $query->orderBy('timestamp', 'asc');
            }])
                ->where('sn', $sn)
                ->first();

            // Jika logger tidak ditemukan, lempar exception
            if (!$data) {
                throw new \Exception('Logger Tidak Ditemukan');
            }

            return $data;
        } catch (\Exception $e) {
            // Tangkap kesalahan dan kirimkan respons dengan pesan kesalahan
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function awlrDetail(Request $request, $sn)
    {
        try {
            // Validasi input, pastikan path parameter sn logger diisi
            if (empty($sn)) {
                throw new \Exception('Nomor Seri Logger harus diisi');
            }

            // Ambil data berdasarkan SN logger yang diinput pengguna
            $data = WlLogger::with('sensors.dataSensor')
                ->where('sn', $sn)
                ->first();

            // Jika logger tidak ditemukan, lempar exception
            if (!$data) {
                throw new \Exception('Logger Tidak Ditemukan');
            }

            return $data;
        } catch (\Exception $e) {
            // Tangkap kesalahan dan kirimkan respons dengan pesan kesalahan
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
