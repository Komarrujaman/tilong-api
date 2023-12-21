<?php

namespace App\Console\Commands;

use App\Http\Controllers\Wl\WLController;
use Illuminate\Console\Command;

class Awlr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:awlr';

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
        $wlController = new WLController();
        $wlController->fetchDataAndSave();

        $this->info('AWLR Data fetched and saved success!');
    }
}
