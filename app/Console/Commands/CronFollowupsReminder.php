<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronFollowupsReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:followup-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send reminders about followups, console version of http://sanityos.com/cron/callbackalert';

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
		$cronController->callBackAlert();
    }
}
