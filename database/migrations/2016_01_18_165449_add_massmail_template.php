<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMassmailTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        Schema::table('leads', function($table) {
            $table->integer('mass_email_template_id')->unsigned()->nullable();
            $table->foreign('mass_email_template_id')->references('id')->on('mass_email_templates')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
        Schema::table('leads', function($table) {
            $table->dropForeign('leads_mass_email_template_id_foreign'); // Drop foreign key 'mass_template_id' from 'leads' table
            $table->dropColumn('mass_email_template_id');
        });
        DB::commit();
    }
}
