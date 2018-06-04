<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailSendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_emails', function($table) {
            $table->increments('id');
            $table->string('email', 255);
            $table->enum('status', ['Pending', 'Sent']);
            $table->enum('sent', ['Now', 'Later']);
            $table->dateTime('after_time')->nullable();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('template_id')->unsigned();
            $table->foreign('template_id')->references('id')->on('mass_email_templates')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
        Schema::dropIfExists('mass_emails');
    }
}
