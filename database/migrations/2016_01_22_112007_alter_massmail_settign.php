<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassmailSettign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('massmailserver_setting', function ($table) {
            $table->dropColumn('access_key', 'secret_key', 'region');

            $table->string('host', 255);
            $table->integer('port');
            $table->string('username', 255);
            $table->string('password', 100);
            $table->enum('security', ['Yes', 'No'])->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('massmailserver_setting', function ($table) {
            $table->string('access_key', 50);
            $table->string('secret_key', 50);
            $table->string('region', 10);

	        $table->dropColumn('host', 'port', 'username', 'password', 'security');
        });
    }
}
