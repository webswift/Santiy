<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionAddTypePlanDetailChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `type` `type` ENUM('New','Renew','CapacityIncrease','CapacityDecrease','TrialRenew','PlanDetailChange') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `type` `type` ENUM('New','Renew','CapacityIncrease','CapacityDecrease','TrialRenew') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`");
    }
}
