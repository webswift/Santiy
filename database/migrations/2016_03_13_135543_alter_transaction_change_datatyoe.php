<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionChangeDatatyoe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `transactions`
	CHANGE COLUMN `nextBillingDate` `nextBillingDate` DATETIME NOT NULL COLLATE 'utf8_unicode_ci' AFTER `nextBillingAmount`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
