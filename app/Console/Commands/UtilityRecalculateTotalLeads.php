<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;

class UtilityRecalculateTotalLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanityos_util:recalculate_total_leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates totalLeads';

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
        $campaigns = Campaign::all();
		foreach($campaigns as $campaign) {
			$campaign->recalculateTotalLeads();
		}
    }
}
