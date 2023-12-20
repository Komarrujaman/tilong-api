<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class getDataHobo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:hobo';

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
        $client = new Client();
        $token = session('token');
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $request = new Request('GET', 'https://webservice.hobolink.com/ws/data/file/JSON/user/30859?loggers=20780458&start_date_time=2023-12-21 00:00:00&end_date_time=2023-12-21 12:00:00', $headers);
        $response = $client->sendAsync($request)->wait();
        $res = json_decode($response->getBody());

        $this->info('Get Token Succes, Token is : ' . $res);
    }
}
