<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SalesmembersCampaignsUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			RENAME TABLE salemembers_campaigns TO salesmembers_campaigns 
		");
		
		DB::statement("
			INSERT INTO salesmembers_campaigns(salesmember_id, campaign_id) 
			SELECT id, campaignid 
			FROM salesmembers
			WHERE campaignid IS NOT NULL
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
