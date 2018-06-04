<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallHistoryNullableNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE call_history 
				MODIFY COLUMN `agent_name` varchar(141) DEFAULT NULL,
				MODIFY COLUMN `call_booked_with_user_name` varchar(141) DEFAULT NULL
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_history', function (Blueprint $table) {
            //
        });
    }
}
