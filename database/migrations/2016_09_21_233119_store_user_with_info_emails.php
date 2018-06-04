<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StoreUserWithInfoEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_info_emails', function (Blueprint $table) {
			$table->integer('user_id')
				->unsigned()
				->nullable()
				->comment('user who sent email')
				;

			$table->foreign('user_id')
				->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

		DB::statement("
			UPDATE lead_info_emails, leads
			SET lead_info_emails.user_id = leads.lastActioner
			WHERE lead_info_emails.lead_id = leads.id
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_info_emails', function (Blueprint $table) {
            //
        });
    }
}
