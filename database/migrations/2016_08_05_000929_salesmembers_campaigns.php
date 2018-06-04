<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SalesmembersCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salemembers_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');

			$table->integer('salesmember_id')->unsigned();
		    $table->foreign('salesmember_id')->references('id')->on('salesmembers')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');
			
			$table->integer('campaign_id')->unsigned();
		    $table->foreign('campaign_id')->references('id')->on('campaigns')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');

			$table->unique(['campaign_id', 'salesmember_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('salemembers_campaigns');
    }
}
