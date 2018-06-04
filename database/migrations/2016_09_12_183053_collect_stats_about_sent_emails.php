<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CollectStatsAboutSentEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_info_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

			$table->integer('lead_id')
				->unsigned()
				;

			$table->foreign('lead_id')
				->references('id')->on('leads')
                ->onUpdate('cascade')
                ->onDelete('cascade');

			$table->integer('campaign_id')
				->unsigned()
				;

			$table->foreign('campaign_id')
				->references('id')->on('campaigns')
                ->onUpdate('cascade')
                ->onDelete('cascade');

			$table->index(['campaign_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lead_info_emails');
    }
}
