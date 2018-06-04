<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptimizeStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
			$table->index(['lastActioner', 'campaignID', 'status', 'timeEdited']);
			$table->index(['campaignID', 'status', 'timeEdited']);
        });
        Schema::table('leadcustomdata', function (Blueprint $table) {
			$table->index(['leadID', 'fieldName']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
