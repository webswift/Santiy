<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronLicenseCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:licensecheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'checks when user licenses ends, console version of http://sanityos.com/cron/licensecheck';

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
		$cronController->LicenseChecking();
    }
}
