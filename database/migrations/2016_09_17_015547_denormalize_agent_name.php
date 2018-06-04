<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DenormalizeAgentName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_history', function (Blueprint $table) {
			$table->string('agent_name', 141);
        });

		DB::statement("
			UPDATE call_history, users 
			SET call_history.agent_name = CONCAT(users.firstName, ' ', users.lastName)
			WHERE users.id = call_history.agent
		");
		
		DB::statement("
			ALTER TABLE call_history 
				DROP KEY FK_call_history_users,
				DROP FOREIGN KEY FK_call_history_users,
				MODIFY COLUMN agent int(10) unsigned DEFAULT NULL 
		");
		
		Schema::table('call_history', function (Blueprint $table) {
		    $table->foreign('agent')->references('id')->on('users')
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
