<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAwsstatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `aws_status` `aws_status` ENUM('Pending','Bounce','Complaint','Delivery') NOT NULL DEFAULT 'Pending' COLLATE 'utf8_unicode_ci' AFTER `opened_at`;
");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `mass_emails`
	CHANGE COLUMN `aws_status` `aws_status` ENUM('Bounce','Complaint','Delivery') NOT NULL DEFAULT 'Delivery' COLLATE 'utf8_unicode_ci' AFTER `opened_at`;
");
    }
}
