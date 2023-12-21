<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hobo\DataSensor;
use App\Models\Hobo\Loggers;
use App\Models\WL\WlLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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
            $query->with(['dataSensor' => function ($subquery) {
                $subquery->select(
                    'sensor_id',
                    'data_type_id',
                    'si_value',
                    'si_unit',
                    'us_value',
                    'us_unit',
                    'scaled_value',
                    'scaled_unit',
                    'sinyal',
                    'timestamp',
                    DB::raw('MAX(timestamp) as latest_timestamp')
                )
                    ->groupBy('sensor_id', 'data_type_id', 'si_value', 'si_unit', 'us_value', 'us_unit', 'scaled_value', 'scaled_unit', 'sinyal', 'timestamp');
            }])
                ->orderBy('id', 'asc');
        }])
            ->get();

        return $data;
    }

    public function awlrLast()
    {
        $data = WlLogger::with(['sensors' => function ($query) {
            $query->with(['dataSensor' => function ($subquery) {
                $subquery->select(
                    'sensor_id',
                    'data_type_id',
                    'si_value',
                    'si_unit',
                    'us_value',
                    'us_unit',
                    'scaled_value',
                    'scaled_unit',
                    'volume',
                    'debit',
                    'level_mdpl',
                    'sinyal',
                    'timestamp',
                    DB::raw('MAX(timestamp) as latest_timestamp')
                )
                    ->groupBy('sensor_id', 'data_type_id', 'si_value', 'si_unit', 'us_value', 'us_unit', 'scaled_value', 'scaled_unit', 'volume', 'debit', 'level_mdpl', 'sinyal', 'timestamp');
            }])
                ->orderBy('id', 'asc');
        }])
            ->get();

        return $data;
    }

    public function awsFilter(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'logger_sn' => 'required', // Sesuaikan dengan nama field di form atau parameter yang diterima
                'start' => 'required',
                'end' => 'required',
            ]);

            $loggerSn = $request->input('logger_sn');
            $start = $request->input('start');
            $end = $request->input('end');

            // Menggunakan eloquent untuk mendapatkan data sesuai filter
            $data = Loggers::where('sn', $loggerSn)
                ->with(['sensors.dataSensor' => function ($query) use ($start, $end) {
                    $query->whereBetween('timestamp', [$start, $end]);
                }])
                ->get();

            // Menyusun data logger dan timestamp
            $loggers = $data->map(function ($logger) {
                return [
                    'logger' => $logger->sn,
                    'timestamp' => $logger->sensors->flatMap(function ($sensor) {
                        return $sensor->dataSensor->pluck('timestamp');
                    })->unique()->values(),
                ];
            });

            return response()->json($loggers);
        } catch (QueryException $exception) {
            // Tangkap eksepsi jika terjadi kesalahan SQL
            return response()->json(['error' => 'Gagal mengambil data. ' . $exception->getMessage()], 500);
        } catch (\Exception $exception) {
            // Tangkap eksepsi umum
            return response()->json(['error' => 'Terjadi kesalahan. ' . $exception->getMessage()], 500);
        }
    }
}
