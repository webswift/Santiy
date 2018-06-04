<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixLicensesStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE licenses 
				DROP PRIMARY KEY,
				MODIFY COLUMN owner int(10) unsigned NOT NULL
		");

		DB::statement("
			DELETE FROM licenses WHERE NOT EXISTS(SELECT 1 FROM users WHERE users.id = licenses.owner);
		");

        Schema::table('licenses', function (Blueprint $table) {
			$table->dropColumn('volume');
			$table->primary('owner');
		    $table->foreign('owner')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function (Blueprint $table) {
            //
        });
    }
}
