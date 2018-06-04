<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixDefaultValueForUserTimezoneForDevDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE users 
				MODIFY COLUMN `timeZone` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '+00:00=29'
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
