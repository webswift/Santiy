<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronStatsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:statsupdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sends team stats, console version of http://sanityos.com/cron/statsupdate';

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
		$cronController->statsUpdate();
    }
}
