<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupportEncryptionTlsSslUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE smtpsettings 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL
		");

		DB::statement("
			UPDATE smtpsettings 
			SET security = 'ssl'
			WHERE security = 'Yes'
		");

		DB::statement("
			ALTER TABLE smtpsettings 
				MODIFY COLUMN `security` enum('No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL
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
			ALTER TABLE smtpsettings 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') COLLATE utf8_unicode_ci NOT NULL
		");

		DB::statement("
			UPDATE smtpsettings 
			SET security = 'Yes'
			WHERE security = 'ssl'
		");
    }
}
