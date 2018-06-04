<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Exception;
use DB;
use DateTime;
use App\Models\MassEmailsLock;

class ClearMassEmailsLocks extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'massmail:clear-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear mass emails locks';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
		DB::unprepared("SET autocommit=0");
		DB::unprepared("LOCK TABLES mass_emails_locks WRITE");
        //delete every locks older then 10 mins
		MassEmailsLock::where('created_at', '<', \DB::raw("(NOW() - INTERVAL 10 MINUTE)")) 
			->delete();
		DB::commit();
		DB::unprepared("UNLOCK TABLES");
    }
}
