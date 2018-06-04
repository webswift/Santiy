<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function($table) {
            $table->dropColumn('merchant_order_no', 'order_no');
            $table->string('plan_id', 255);
            $table->string('payer_id', 255);
            $table->string('state', 255);
            $table->enum('currency', ['USD', 'EUR', 'GBP'])->default('GBP');
        });

        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `type` `type` ENUM('New','Renew','CapacityIncrease','CapacityDecrease','TrialRenew') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function($table) {
            $table->dropColumn('plan_id', 'payer_id');

            $table->string('merchant_order_no', 100);
            $table->string('order_no', 100);
        });
    }
}
