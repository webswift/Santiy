<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupportEncryptionTlsSsl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE massmailserver_setting 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No'
		");

		DB::statement("
			UPDATE massmailserver_setting 
			SET security = 'tls'
			WHERE security = 'Yes'
		");

		DB::statement("
			ALTER TABLE massmailserver_setting 
				MODIFY COLUMN `security` enum('No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No'
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::statement("
			ALTER TABLE massmailserver_setting 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No'
		");

		DB::statement("
			UPDATE massmailserver_setting 
			SET security = 'Yes'
			WHERE security = 'tls'
		");
    }
}
