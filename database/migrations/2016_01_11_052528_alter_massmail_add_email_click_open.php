<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassmailAddEmailClickOpen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_emails', function($table) {
            $table->enum('is_email_open', ['No', 'Yes'])->default('No');
            $table->integer('email_open_count');

            $table->enum('is_email_click', ['No', 'Yes'])->default('No');
            $table->integer('email_click_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_emails', function($table) {
            $table->dropColumn('is_email_open', 'email_open_count', 'is_email_click', 'email_click_count');
        });
    }
}
