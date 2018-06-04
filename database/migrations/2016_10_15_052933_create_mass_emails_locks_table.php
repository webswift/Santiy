<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMassEmailsLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_emails_locks', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
			$table->integer('mass_email_id')->unsigned();
			$table->unique('mass_email_id');
		    $table->foreign('mass_email_id')->references('id')->on('mass_emails')
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
        Schema::drop('mass_emails_locks');
    }
}
