<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassEmailTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_email_templates', function($table) {
            $table->enum('status', ['Save as draft', 'Scheduled', 'Sent'])->default('Save as draft');
	        $table->integer('email_sent');
	        $table->dateTime('schedule_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_email_templates', function($table) {
	       $table->dropColumn('status', 'email_sent', 'schedule_time');
        });
    }
}
