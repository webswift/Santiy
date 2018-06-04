<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PendingTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions_pending', function (Blueprint $table) {
            $table->string('id', 128);
			$table->primary('id');
            $table->string('state', 128);
			$table->timestamp('start_date');
            $table->string('plan_id', 128);
            $table->string('payer_id', 128);
			
			$table->integer('user_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')
			    ->onUpdate('cascade')
			    ->onDelete('cascade');

			$table->enum('currency', ['USD','EUR','GBP']);
			$table->decimal('amount', 10,2);
			$table->enum('type', ['New','Renew','CapacityIncrease','CapacityDecrease','TrialRenew','PlanDetailChange','Resubscribe']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions_pending');
    }
}
