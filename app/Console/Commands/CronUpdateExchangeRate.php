<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronUpdateExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:exchangerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get exchange rates for eur and gbp, console version of http://sanityos.com/cron/exchangerate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronController = new \App\Http\Controllers\CronJobController;
		$cronController->exchangeRateScrap();
    }
}
