<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassemailsAddPostpone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent', 'Postpone') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");
    }
}
