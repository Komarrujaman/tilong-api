<?php

namespace App\Console\Commands;

use App\Http\Controllers\Hobo\HoboController;
use App\Models\Hobo\Hobo;
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
        $hoboController = new HoboController();
        $hoboController->fetchDataAndSave();
        $wlController = new HoboController();
        $wlController->saveAwlr();

        $this->info('AWS Data fetched and saved successfully!');
    }
}
