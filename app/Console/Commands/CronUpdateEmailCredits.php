<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronUpdateEmailCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-emails-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset limits of mass emails, console version of http://sanityos.com/cron/emailCredits';

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
		$cronController->emailCredits();
    }
}
