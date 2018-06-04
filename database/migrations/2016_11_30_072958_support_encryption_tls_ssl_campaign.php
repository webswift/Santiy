<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupportEncryptionTlsSslCampaign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE campaign_email_settings 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') NOT NULL DEFAULT 'No'
		");

		DB::statement("
			UPDATE campaign_email_settings 
			SET security = 'ssl'
			WHERE security = 'Yes'
		");

		DB::statement("
			ALTER TABLE campaign_email_settings 
				MODIFY COLUMN `security` enum('No', 'ssl', 'tls') NOT NULL DEFAULT 'No'
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
			ALTER TABLE campaign_email_settings 
				MODIFY COLUMN `security` enum('Yes','No', 'ssl', 'tls') NOT NULL DEFAULT 'No'
		");

		DB::statement("
			UPDATE campaign_email_settings 
			SET security = 'Yes'
			WHERE security = 'ssl'
		");
    }
}
