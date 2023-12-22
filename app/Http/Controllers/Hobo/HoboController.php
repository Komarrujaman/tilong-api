<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use App\Models\Hobo\{Hobo, Loggers, Sensors, DataSensor};
use App\Models\WL\WlDataSensor;
use App\Models\WL\WlLogger;
use App\Models\WL\WlSensor;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\RequestException;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class HoboController extends Controller
{
    public function login()
    {
        $client = new Client();
        $clientId = 'TelkomIot_WS';
        $clientSecret = '69ee15145be52a2055e96ac1d4492ec0d1971e61';
        $authorization = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $authorization
        ];

        $options = [
            'form_params' => [
                'client_id' => 'TelkomIot_WS',
                'client_secret' => '69ee15145be52a2055e96ac1d4492ec0d1971e61',
                'grant_type' => 'client_credentials'
            ]
        ];

        $request = new Request('POST', 'https://webservice.hobolink.com/ws/auth/token', $headers);
        $response = $client->sendAsync($request, $options)->wait();
        $res = json_decode($response->getBody());
        Session::put('token_hobo', $res->access_token);
        return $res;
    }

    public function aws()
    {
        // Periksa apakah token tersedia di session
        if (!Session::has('token_hobo')) {
            // Jika token tidak tersedia, panggil fungsi login
            $loginResult = $this->login();

            // Periksa apakah login berhasil
            if (property_exists($loginResult, 'access_token')) {
                // Jika berhasil, simpan token ke dalam session
                Session::put('token_hobo', $loginResult->access_token);

                // Coba kembali permintaan API dengan token baru
                return $this->aws();
            } else {
                // Jika login gagal, kembalikan respons kesalahan
                return response()->json(['error' => 'Login failed.'], 401);
            }
        }

        $client = new Client();
        $token = Session::get('token_hobo');
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $aws_logger = Loggers::allLogger();
        $observationList = [];

        foreach ($aws_logger as $index) {
            $sn  = $index['logger_sn'];
            try {
                $currentTimeUTC = Carbon::now('UTC');
                $startDate = $currentTimeUTC->copy()->subHour(); // Mengurangkan waktu sebanyak 1 jam dari waktu UTC sekarang
                $endDate = $currentTimeUTC; // Menggunakan waktu UTC sekarang

                $request = new Request('GET', 'https://webservice.hobolink.com/ws/data/file/JSON/user/30859?loggers=' . $sn . '&start_date_time=' . $startDate->toDateTimeString() . '&end_date_time=' . $endDate->toDateTimeString(), $headers);
                $response = $client->sendAsync($request)->wait();
                $res = json_decode($response->getBody());
                // Konversi timestamp dari UTC ke zona waktu Samarinda
                foreach ($res->{'observation_list'} as $observation) {
                    $utcTimestamp = new DateTime($observation->timestamp, new DateTimeZone('UTC'));
                    $utcTimestamp->setTimezone(new DateTimeZone('Asia/Makassar')); // Ganti dengan 'Asia/Samarinda' jika perlu

                    $observation->timestamp = $utcTimestamp->format('Y-m-d H:i:s');
                }

                $observationList = array_merge($observationList, $res->{'observation_list'});
            } catch (RequestException $e) {
                // Lakukan penanganan kesalahan, misalnya token kadaluwarsa
                if ($e->getResponse() && $e->getResponse()->getStatusCode() == 401) {
                    // Jika mendeteksi token kadaluwarsa, panggil fungsi login
                    $loginResult = $this->login();

                    // Periksa apakah login berhasil
                    if (property_exists($loginResult, 'access_token')) {
                        // Jika berhasil, simpan token ke dalam session
                        Session::put('token_hobo', $loginResult->access_token);

                        // Coba kembali permintaan API dengan token baru
                        return $this->aws();
                    } else {
                        // Jika login gagal, kembalikan respons kesalahan
                        return response()->json(['error' => 'Login failed.'], 401);
                    }
                }

                // Penanganan kesalahan lainnya
                return response()->json(['error' => 'Something went wrong.'], 500);
            }
        }
        return $observationList;
    }

    public function awsTest()
    {
        // Panggil fungsi aws
        $result = $this->aws();

        // Tampilkan hasil sebagai JSON
        return Response::json($result);
    }

    public function saveDataFromApi($apiData)
    {
        foreach ($apiData as $apiDatum) {
            // Convert the stdClass object to an array
            $apiDatumArray = json_decode(json_encode($apiDatum), true);

            // Cek dan simpan logger
            $logger = Loggers::firstOrNew(['sn' => $apiDatumArray['logger_sn']]);
            // Set atribut logger sesuai data yang diterima
            $logger->save();

            // Cek dan simpan sensor
            $sensor = Sensors::firstOrNew([
                'logger_id' => $logger->id,
                'sensor_sn' => $apiDatumArray['sensor_sn'],
            ]);
            // Set atribut sensor sesuai data yang diterima
            $sensor->sensor_key = $apiDatumArray['sensor_key'];
            $sensor->measurement_type = $apiDatumArray['sensor_measurement_type'];
            $sensor->save();

            // Cek apakah data sensor dengan timestamp yang sama sudah ada
            $existingDataSensor = DataSensor::where([
                'sensor_id' => $sensor->id,
                'timestamp' => $apiDatumArray['timestamp'],
            ])->first();

            if (!$existingDataSensor) {
                // Simpan data sensor karena belum ada data dengan timestamp yang sama
                $dataSensor = new DataSensor([
                    'sensor_id' => $sensor->id,
                    'data_type_id' => $apiDatumArray['data_type_id'],
                    'si_value' => $apiDatumArray['si_value'],
                    'si_unit' => $apiDatumArray['si_unit'],
                    'us_value' => $apiDatumArray['us_value'],
                    'us_unit' => $apiDatumArray['us_unit'],
                    'scaled_value' => $apiDatumArray['scaled_value'],
                    'scaled_unit' => $apiDatumArray['scaled_unit'],
                    'timestamp' => $apiDatumArray['timestamp'],
                ]);
                $dataSensor->save();
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }
    public function fetchDataAndSave()
    {
        // Panggil metode aws untuk mendapatkan data
        $apiData = $this->aws();

        // Panggil metode saveDataFromApi untuk menyimpan data
        $response = $this->saveDataFromApi($apiData);

        return $response;
    }






    public function awlr()
    {
        // Periksa apakah token tersedia di session
        if (!Session::has('token_hobo')) {
            // Jika token tidak tersedia, panggil fungsi login
            $loginResult = $this->login();

            // Periksa apakah login berhasil
            if (property_exists($loginResult, 'access_token')) {
                // Jika berhasil, simpan token ke dalam session
                Session::put('token_hobo', $loginResult->access_token);

                // Coba kembali permintaan API dengan token baru
                return $this->awlr();
            } else {
                // Jika login gagal, kembalikan respons kesalahan
                return response()->json(['error' => 'Login failed.'], 401);
            }
        }

        $client = new Client();
        $token = Session::get('token_hobo');
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];
        $wl_logger = WlLogger::allLogger();
        $observationList = [];

        foreach ($wl_logger as $index) {
            $sn = $index['logger_sn'];
            try {
                $currentTimeUTC = Carbon::now('UTC');
                $startDate = $currentTimeUTC->copy()->subHour(12); // Mengurangkan waktu sebanyak 1 jam dari waktu UTC sekarang
                $endDate = $currentTimeUTC; // Menggunakan waktu UTC sekarang

                $request = new Request('GET', 'https://webservice.hobolink.com/ws/data/file/JSON/user/30859?loggers=' . $sn . '&start_date_time=' . $startDate->toDateTimeString() . '&end_date_time=' . $endDate->toDateTimeString(), $headers);
                $response = $client->sendAsync($request)->wait();
                $res = json_decode($response->getBody());
                $observationList = array_merge($observationList, $res->{'observation_list'});
            } catch (RequestException $e) {
                // Lakukan penanganan kesalahan, misalnya token kadaluwarsa
                if ($e->getResponse() && $e->getResponse()->getStatusCode() == 401) {
                    // Jika mendeteksi token kadaluwarsa, panggil fungsi login
                    $loginResult = $this->login();

                    // Periksa apakah login berhasil
                    if (property_exists($loginResult, 'access_token')) {
                        // Jika berhasil, simpan token ke dalam session
                        Session::put('token_hobo', $loginResult->access_token);

                        // Coba kembali permintaan API dengan token baru
                        return $this->awlr();
                    } else {
                        // Jika login gagal, kembalikan respons kesalahan
                        return response()->json(['error' => 'Login failed.'], 401);
                    }
                }

                // Penanganan kesalahan lainnya
                return response()->json(['error' => 'Something went wrong.'], 500);
            }
        }
        return $observationList;
    }

    public function saveAwlrHobo($apiData)
    {
        foreach ($apiData as $apiDatum) {
            // Convert the stdClass object to an array
            $apiDatumArray = json_decode(json_encode($apiDatum), true);

            // Cek dan simpan logger
            $logger = WlLogger::firstOrNew(['sn' => $apiDatumArray['logger_sn']]);
            // Set atribut logger sesuai data yang diterima
            $logger->save();

            // Cek dan simpan sensor
            $sensor = WlSensor::firstOrNew([
                'logger_id' => $logger->id,
                'sensor_sn' => $apiDatumArray['sensor_sn'],
            ]);
            // Set atribut sensor sesuai data yang diterima
            $sensor->sensor_key = $apiDatumArray['sensor_key'];
            $sensor->measurement_type = $apiDatumArray['sensor_measurement_type'];
            $sensor->save();

            // Cek apakah data sensor dengan timestamp yang sama sudah ada
            $existingDataSensor = WlDataSensor::where([
                'sensor_id' => $sensor->id,
                'timestamp' => $apiDatumArray['timestamp'],
            ])->first();

            if (!$existingDataSensor) {
                // Simpan data sensor karena belum ada data dengan timestamp yang sama
                $dataSensor = new WlDataSensor([
                    'sensor_id' => $sensor->id,
                    'data_type_id' => $apiDatumArray['data_type_id'],
                    'si_value' => $apiDatumArray['si_value'],
                    'si_unit' => $apiDatumArray['si_unit'],
                    'us_value' => $apiDatumArray['us_value'],
                    'us_unit' => $apiDatumArray['us_unit'],
                    'scaled_value' => $apiDatumArray['scaled_value'],
                    'scaled_unit' => $apiDatumArray['scaled_unit'],
                    'timestamp' => $apiDatumArray['timestamp'],
                ]);
                $dataSensor->save();
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }
    public function saveAwlr()
    {
        // Panggil metode aws untuk mendapatkan data
        $apiData = $this->awlr();

        // Panggil metode saveAwlrHobo untuk menyimpan data
        $response = $this->saveAwlrHobo($apiData);

        return $response;
    }
}
