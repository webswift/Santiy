<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MassMailTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_email_templates', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('from_name', 50);
            $table->string('reply_to', 255);
            $table->string('name', 100);
            $table->string('subject', 100);
            $table->text('content');
            $table->enum('type', ['campaign', 'custom'])->default('campaign');
            $table->integer('campaign_id')->unsigned()->nullable();
            $table->foreign('campaign_id')->references('id')->on('campaigns')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->text('leads')->nullable();
            $table->text('filter')->nullable();
            $table->timestamps();
        });

        Schema::create('mass_email_attachments', function($table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->string('file_name', 255);
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
        Schema::dropIfExists('mass_email_templates');
        Schema::dropIfExists('mass_email_attachments');
    }
}
