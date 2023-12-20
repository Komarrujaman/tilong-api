<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hobo\Hobo;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class LoginHobo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        session()->forget('token');
        $client = new Client();
        $clientId = 'TelkomIot_WS';
        $clientSecret = '69ee15145be52a2055e96ac1d4492ec0d1971e61';

        // Menggunakan base64_encode untuk membuat Authorization header
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
        $get_token = $res->{'access_token'};
        session(['hobo_token' => $get_token]);
        $token = session('hobo_token');
        $this->info('Get Token Succes, Token is : ' . $token);
    }
}
