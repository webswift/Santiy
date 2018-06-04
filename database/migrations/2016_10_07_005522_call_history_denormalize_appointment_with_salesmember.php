<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallHistoryDenormalizeAppointmentWithSalesmember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_history', function (Blueprint $table) {
			$table->string('appointment_with_sales_name', 141)->nullable();
        });

		DB::statement("
			UPDATE call_history, salesmembers 
			SET call_history.appointment_with_sales_name = CONCAT(salesmembers.firstName, ' ', salesmembers.lastName)
			WHERE salesmembers.id = call_history.appointment
		");
		
		DB::statement("
			ALTER TABLE call_history 
				DROP KEY FK_call_history_salesmembers,
				DROP FOREIGN KEY FK_call_history_salesmembers
		");
		
		Schema::table('call_history', function (Blueprint $table) {
		    $table->foreign('appointment')->references('id')->on('salesmembers')
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
