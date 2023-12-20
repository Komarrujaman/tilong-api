<?php

namespace App\Models\Hobo;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Redis;

class Hobo extends Model
{
    use HasFactory;

    public static function loginHobo()
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
        return $res;
    }


    public static function getData()
    {
        $client = new Client();
        $token = session('token');
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $request = new Request('GET', 'https://webservice.hobolink.com/ws/data/file/JSON/user/30859?loggers=20780458&start_date_time=2023-12-20 00:00:00&end_date_time=2023-12-20 12:00:00', $headers);
        $response = $client->sendAsync($request)->wait();
        $res = json_decode($response->getBody());

        return $res;
    }
}
