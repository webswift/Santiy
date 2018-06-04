<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixReferenceOnUsersDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('massmailserver_setting', function (Blueprint $table) {
			$table->dropForeign(['user_id']);
		    $table->foreign('user_id')->references('id')->on('users')
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
        Schema::table('massmailserver_setting', function (Blueprint $table) {
            //
        });
    }
}
