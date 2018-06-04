<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HavePrevNextFilterPerUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_prevnext_filters', function (Blueprint $table) {
            $table->bigIncrements('id');

			$table->integer('campaign_id')->unsigned();
		    $table->foreign('campaign_id')->references('id')->on('campaigns')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');

			$table->integer('user_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');

			$table->unique(['campaign_id', 'user_id']);
			
			$table->mediumText('prevNextFilter')->nullable();

            $table->integer('totalFilteredLeads')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('campaign_prevnext_filters');
    }
}
