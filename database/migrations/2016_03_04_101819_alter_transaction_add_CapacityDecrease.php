<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionAddCapacityDecrease extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `type` `type` ENUM('New','Renew','CapacityIncrease','CapacityDecrease') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `type` `type` ENUM('New','Renew','CapacityIncrease') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`");
    }
}
