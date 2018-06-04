<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PendingTransactionsFixId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions_pending', function (Blueprint $table) {
			$table->dropColumn('id');
        });
        Schema::table('transactions_pending', function (Blueprint $table) {
			$table->bigIncrements('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions_pending', function (Blueprint $table) {
            //
        });
    }
}
