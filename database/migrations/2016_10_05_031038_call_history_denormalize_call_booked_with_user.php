<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallHistoryDenormalizeCallBookedWithUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_history', function (Blueprint $table) {
			$table->string('call_booked_with_user_name', 141);
        });

		DB::statement("
			UPDATE call_history, users 
			SET call_history.call_booked_with_user_name = CONCAT(users.firstName, ' ', users.lastName)
			WHERE users.id = call_history.callBookedWith
		");
		
		DB::statement("
			ALTER TABLE call_history 
				DROP KEY FK_call_history_users_2,
				DROP FOREIGN KEY FK_call_history_users_2
		");
		
		Schema::table('call_history', function (Blueprint $table) {
		    $table->foreign('callBookedWith')->references('id')->on('users')
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
