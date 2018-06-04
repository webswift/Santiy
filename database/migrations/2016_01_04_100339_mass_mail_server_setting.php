<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MassMailServerSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('massmailserver_setting', function($table) {
            $table->increments('id');
            $table->string('access_key', 50);
            $table->string('secret_key', 50);
            $table->string('region', 10);
            $table->string('from_mail', 255);
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('massmailserver_setting');
    }
}
