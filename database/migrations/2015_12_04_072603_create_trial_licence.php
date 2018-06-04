<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialLicence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licensetypes', function($table) {
            $table->enum('type', ['Trial', 'Paid'])->default('Paid');
        });

	    DB::table('licensetypes')->insert([
		    ['licenseClass' => 'Single', 'name' => 'Single User Trial License', 'expiresIn' => 0,
		     'description' => 'Single user trial licence. Created only for developer needs', 'volume' => 0, 'priceUSD' => 0,
		     'priceGBP' => 0, 'priceEuro' => 0, 'added' => \Carbon\Carbon::now(), 'type' => 'Trial'],
		    ['licenseClass' => 'Multi', 'name' => 'Multi User Trial License', 'expiresIn' => 0,
		     'description' => 'Multi user trial licence. Created only for developer needs', 'volume' => 17, 'priceUSD' => 0,
		     'priceGBP' => 0, 'priceEuro' => 0, 'added' => \Carbon\Carbon::now(), 'type' => 'Trial' ]
	    ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licensetypes', function($table) {
	        $table->dropColumn('type');
        });
    }
}
