<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MassEmailTemplatesSparkpost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE mass_email_templates 
				MODIFY COLUMN `mail_setting_type` enum('Superadmin','User','SparkPost') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Superadmin'
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_email_templates', function (Blueprint $table) {
            //
        });
    }
}
