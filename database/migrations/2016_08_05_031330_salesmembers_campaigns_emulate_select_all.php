<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SalesmembersCampaignsEmulateSelectAll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			INSERT IGNORE INTO salesmembers_campaigns(salesmember_id, campaign_id) 
			SELECT sm.id, c.id 
			FROM salesmembers sm, campaignmembers cmm, campaigns c
			WHERE sm.campaignid IS NULL
				AND cmm.userID = sm.manager
				AND c.id = cmm.campaignID
				AND c.status NOT IN('Completed', 'Archived')
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
