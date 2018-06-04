<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MailTemplatesSizeToMediumtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE `mass_email_templates` 
				CHANGE COLUMN `content` `content` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL ;
		");
		DB::statement("
			ALTER TABLE `emailtemplates` 
				CHANGE COLUMN `templateText` `templateText` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL ;
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
