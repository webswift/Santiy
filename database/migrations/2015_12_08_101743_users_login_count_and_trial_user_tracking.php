<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersLoginCountAndTrialUserTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table) {
            $table->integer('loginCount')->unsigned();
        });

	    Schema::create('trial_user_track', function($table) {
			$table->increments('id');

		    $table->integer('user_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');

		    $table->enum('type', ['Trial', 'Converted', 'Expired'])->default('Trial');
		    $table->timestamps();
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {
			$table->dropColumn('loginCount');
        });

	    Schema::dropIfExists('trial_user_track');

    }
}
