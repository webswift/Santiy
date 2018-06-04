<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronUpdatePaypalTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-paypal-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get info about pp transactions, console version of http://sanityos.com/cron/getTransactions';

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
		$cronController->getTransactionForAgreement();
    }
}
