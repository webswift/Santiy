<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassemailsAddFailed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent', 'Postpone', 'Failed') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");

        Schema::table('mass_emails', function($table) {
            $table->string('fail_reason', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent', 'Postpone') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");

        Schema::table('mass_emails', function($table) {
            $table->dropColumn('fail_reason');
        });
    }
}
