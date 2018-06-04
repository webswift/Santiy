<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EmailTemplateDisableRecipientByDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE emailtemplates 
			MODIFY COLUMN `status` enum('Enable','Disable') DEFAULT 'Disable'
		");
		DB::statement("
			UPDATE emailtemplates 
			SET status = 'Disable'
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
			ALTER TABLE emailtemplates 
			MODIFY COLUMN `status` enum('Enable','Disable') DEFAULT 'Enable'
		");
		DB::statement("
			UPDATE emailtemplates 
			SET status = 'Enable'
		");
    }
}
