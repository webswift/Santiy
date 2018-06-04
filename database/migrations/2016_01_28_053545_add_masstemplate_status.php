<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasstemplateStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('massmailserver_setting', function($table) {
            $table->enum('status', ['Enable', 'Disable'])->default('Enable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('massmailserver_setting', function($table) {
            $table->dropColumn('status');
        });
    }
}
