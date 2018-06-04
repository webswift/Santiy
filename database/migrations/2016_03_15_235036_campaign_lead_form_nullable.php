<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CampaignLeadFormNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement('
			ALTER TABLE campaigns 
			MODIFY COLUMN formID int(10) unsigned
		');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::statement('
			ALTER TABLE campaigns 
			MODIFY COLUMN formID int(10) unsigned NOT NULL
		');
    }
}
