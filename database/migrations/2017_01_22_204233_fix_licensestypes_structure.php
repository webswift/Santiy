<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixLicensestypesStructure extends Migration
{
    /**
     * Run the migrations.
	 *
	 * adds free_users,max_users columns
	 * removes 'volume' column 
	 * fix type for discount
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licensetypes', function (Blueprint $table) {
			$table
				->smallInteger('free_users')
				->unsigned()
				->default(0)
				->after('volume')
				->comment('Count of free users')
				;
			$table
				->smallInteger('max_users')
				->unsigned()
				->default(0)
				->after('free_users')
				->comment('Max count of users, 0 - unlimited');
        });

        Schema::table('licensetypes', function (Blueprint $table) {
			$table->dropColumn('volume');
        });

		DB::statement("
			ALTER TABLE licensetypes 
				MODIFY COLUMN discount smallint(6) unsigned NOT NULL DEFAULT 0
		");

		DB::statement("
			UPDATE licensetypes
			SET max_users = 17
			WHERE type = 'Trial' AND licenseClass='Multi'
			
		");

        Schema::table('licenses', function (Blueprint $table) {
			$table
				->smallInteger('free_users')
				->unsigned()
				->default(0)
				->after('licenseVolume')
				->comment('Count of free users')
				;
			$table
				->smallInteger('max_users')
				->unsigned()
				->default(0)
				->after('free_users')
				->comment('Max count of users, 0 - unlimited');
        });
		
		DB::statement("
			ALTER TABLE licenses 
				MODIFY COLUMN discount smallint(6) unsigned NOT NULL DEFAULT 0
		");

		DB::statement("
			UPDATE licenses, licensetypes
			SET licenses.max_users = licensetypes.max_users
			WHERE licenses.licenseType = licensetypes.id
			
		");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licensetypes', function (Blueprint $table) {
            //
        });
    }
}
