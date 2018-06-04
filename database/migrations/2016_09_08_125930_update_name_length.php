<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNameLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE `formfields` 
				CHANGE COLUMN `fieldName` `fieldName` varchar(150) COLLATE utf8_unicode_ci NOT NULL ;
		");
		DB::statement("
			ALTER TABLE `landingformfields` 
				CHANGE COLUMN `fieldName` `fieldName` varchar(150) COLLATE utf8_unicode_ci NOT NULL ;
		");
		DB::statement("
			ALTER TABLE `leadcustomdata` 
				CHANGE COLUMN `fieldName` `fieldName` varchar(150) COLLATE utf8_unicode_ci NOT NULL ;
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formfields', function (Blueprint $table) {
            //
        });
    }
}
