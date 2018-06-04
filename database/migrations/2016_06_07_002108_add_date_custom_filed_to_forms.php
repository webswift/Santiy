<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateCustomFiledToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE formfields 
			MODIFY COLUMN type enum('text','dropdown','date') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text'
		");
		
		DB::statement("
			ALTER TABLE landingformfields 
			MODIFY COLUMN type enum('text','dropdown','date') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text'
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
