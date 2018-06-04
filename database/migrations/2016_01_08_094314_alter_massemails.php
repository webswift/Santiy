<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassemails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_emails', function($table) {
            $table->mediumText('lead_data');
        });

        Schema::table('mass_email_templates', function($table) {
            DB::statement("ALTER TABLE `mass_email_templates`
	CHANGE COLUMN `filter` `filter` MEDIUMTEXT NULL COLLATE 'utf8_unicode_ci' AFTER `leads`");

            DB::statement("ALTER TABLE `mass_email_templates`
	CHANGE COLUMN `leads` `leads` LONGTEXT NULL COLLATE 'utf8_unicode_ci' AFTER `campaign_id`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_emails', function($table) {
            $table->dropColumn('lead_data');
        });
    }
}
