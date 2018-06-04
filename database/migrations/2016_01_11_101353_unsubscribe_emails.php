<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnsubscribeEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_unsubscribes', function($table)
        {
            $table->increments('id');
            $table->string('email', 255);
            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaigns')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('custom_unsubscribes', function($table) {
            $table->increments('id');
            $table->string('email', 255);
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent','Postpone','Failed', 'Unsubscribed') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_unsubscribes');
        Schema::dropIfExists('custom_unsubscribes');

        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `status` `status` ENUM('Pending','Sent','Postpone','Failed') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `email`");
    }
}
