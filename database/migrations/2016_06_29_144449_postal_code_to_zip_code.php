<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PostalCodeToZipCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			UPDATE formfields
			SET fieldName = 'Post/Zip code'
			WHERE fieldName = 'Postal Code'
			");
		
		DB::statement("
			UPDATE landingformfields 
			SET fieldName = 'Post/Zip code'
			WHERE fieldName = 'Postal Code'
			");
		
		DB::statement("
			UPDATE leadcustomdata 
			SET fieldName = 'Post/Zip code'
			WHERE fieldName = 'Postal Code'
			");

		DB::statement("
			UPDATE mass_emails 
			SET lead_data = REPLACE(lead_data, 'Postal Code', 'Post/Zip code')
			WHERE lead_data LIKE '%Postal Code%'
			");
		
		DB::statement("
			UPDATE emailtemplates 
			SET templateText = REPLACE(templateText, 'Postal Code', 'Post/Zip code')
			WHERE templateText LIKE '%Postal Code%'
			");
		
		DB::statement("
			UPDATE mass_email_templates 
			SET content = REPLACE(content, 'Postal Code', 'Post/Zip code')
			WHERE content LIKE '%Postal Code%'
			");
		
		DB::statement("
			UPDATE mass_email_templates 
			SET leads = REPLACE(leads, 'Postal Code', 'Post/Zip code')
			WHERE leads LIKE '%Postal Code%'
			");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
