<?php

namespace App\Http\Controllers\Hobo;

use App\Http\Controllers\Controller;
use App\Models\Hobo\{Hobo, Loggers, Sensors, DataSensor};
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\RequestException;

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

        try {
            $request = new Request('GET', 'https://webservice.hobolink.com/ws/data/file/JSON/user/30859?loggers=20780458&start_date_time=2023-12-20 00:00:00&end_date_time=2023-12-20 12:00:00', $headers);
            $response = $client->sendAsync($request)->wait();
            $res = json_decode($response->getBody());

            return $res->{'observation_list'};
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

    public function saveDataFromApi($apiData)
    {
        foreach ($apiData as $apiDatum) {
            // Convert the stdClass object to an array
            $apiDatumArray = json_decode(json_encode($apiDatum), true);

            // Cek dan simpan logger
            $logger = Loggers::firstOrNew(['sn' => $apiDatumArray['logger_sn']]);
            // Set atribut logger sesuai data yang diterima
            $logger->nama = 'Nama Logger'; // Ganti dengan nama logger yang sesuai
            $logger->lat = 'Latitude'; // Ganti dengan data yang sesuai
            $logger->lng = 'Longitude'; // Ganti dengan data yang sesuai
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
}
