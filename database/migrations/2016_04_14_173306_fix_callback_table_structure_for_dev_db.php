<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCallbackTableStructureForDevDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE callbacks 
				MODIFY COLUMN `emailSent` enum('True','False') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'False'
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
