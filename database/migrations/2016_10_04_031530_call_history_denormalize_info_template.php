<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallHistoryDenormalizeInfoTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_history', function (Blueprint $table) {
			$table->string('email_template_name', 50)->nullable();
        });

		DB::statement("
			UPDATE call_history, emailtemplates 
			SET call_history.email_template_name = emailtemplates.name
			WHERE emailtemplates.id = call_history.emailTemplate
		");
		
		DB::statement("
			ALTER TABLE call_history 
				DROP KEY FK_call_history_emailtemplates,
				DROP FOREIGN KEY FK_call_history_emailtemplates
		");
		
		Schema::table('call_history', function (Blueprint $table) {
		    $table->foreign('emailTemplate')->references('id')->on('emailtemplates')
                ->onUpdate('cascade')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_history', function (Blueprint $table) {
            //
        });
    }
}
