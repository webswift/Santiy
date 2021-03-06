<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMassmailTemplateCallhistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_history', function($table) {
            $table->integer('mass_email_template_id')->unsigned()->nullable();
            $table->foreign('mass_email_template_id')->references('id')->on('mass_email_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_history', function($table) {
            $table->dropForeign('call_history_mass_email_template_id_foreign'); // Drop foreign key 'mass_email_template_id' from 'call_history' table
            $table->dropColumn('mass_email_template_id');
        });
    }
}
