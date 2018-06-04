<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeCampaignmembersUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE campaignmembers
				ADD COLUMN id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				ADD PRIMARY KEY (`id`)
			;
		");
		DB::statement("
			DELETE FROM c1 
			USING campaignmembers AS c1, campaignmembers AS c2
			WHERE c1.userID = c2.userID
				AND c1.campaignID = c2.campaignID 
				AND c1.id > c2.id
		");
        Schema::table('campaignmembers', function (Blueprint $table) {
			$table->unique(['userID', 'campaignID']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaignmembers', function (Blueprint $table) {
            //
        });
    }
}
