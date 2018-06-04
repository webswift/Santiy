<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UseTimestampsInsteadDatetime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::statement("
			ALTER TABLE admins 
				MODIFY COLUMN `lastLogin` timestamp NOT NULL DEFAULT 0
		");
		DB::statement("
			ALTER TABLE appointments 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE callbacks 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE campaigns 
				MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `timeStarted` timestamp NULL DEFAULT NULL 
				, MODIFY COLUMN `completedOn` timestamp NULL DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE discountcodes 
				MODIFY COLUMN `added` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE emailtemplates 
				MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE forms 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE helparticles 
				MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `timeEdited` timestamp NULL DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE helptopics 
				MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE leads 
				MODIFY COLUMN `timeCreated` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `timeEdited` timestamp NULL DEFAULT NULL 
				, MODIFY COLUMN `timeOpened` timestamp NULL DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE licenses 
				MODIFY COLUMN `purchaseTime` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `expireDate` timestamp NOT NULL  DEFAULT 0 
		");
		DB::statement("
			ALTER TABLE licensetypes 
				MODIFY COLUMN `added` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE mass_email_templates 
				MODIFY COLUMN `schedule_time` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE mass_emails 
				MODIFY COLUMN `after_time` timestamp NULL DEFAULT NULL 
				, MODIFY COLUMN `opened_at` timestamp NULL DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE pushmessages 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE salesmembers 
				MODIFY COLUMN `creationDate` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE statistics_share_info 
				MODIFY COLUMN `customRangeFrom` timestamp NULL DEFAULT NULL 
				, MODIFY COLUMN `customRangeTo` timestamp NULL DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE todolists 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
		");
		DB::statement("
			ALTER TABLE transactions 
				MODIFY COLUMN `time` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `nextBillingDate` timestamp NOT NULL  DEFAULT 0 
		");
		DB::statement("
			ALTER TABLE users 
				MODIFY COLUMN `accountCreationDate` timestamp NOT NULL  DEFAULT 0
				, MODIFY COLUMN `lastLogin` timestamp NOT NULL  DEFAULT 0 
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::statement("
			ALTER TABLE admins 
				MODIFY COLUMN `lastLogin` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE appointments 
				MODIFY COLUMN `time` datetime NOT NULL 
				, MODIFY COLUMN `timeCreated` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE callbacks 
				MODIFY COLUMN `time` datetime NOT NULL 
				, MODIFY COLUMN `timeCreated` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE campaigns 
				MODIFY COLUMN `timeCreated` datetime NOT NULL 
				, MODIFY COLUMN `timeStarted` datetime DEFAULT NULL 
				, MODIFY COLUMN `completedOn` datetime DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE discountcodes 
				MODIFY COLUMN `added` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE emailtemplates 
				MODIFY COLUMN `timeCreated` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE forms 
				MODIFY COLUMN `time` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE helparticles 
				MODIFY COLUMN `timeCreated` datetime NOT NULL 
				, MODIFY COLUMN `timeEdited` datetime DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE helptopics 
				MODIFY COLUMN `timeCreated` datetime NOT NULL 
		");
		DB::statement("
			ALTER TABLE leads 
				MODIFY COLUMN `timeCreated` datetime NOT NULL 
				, MODIFY COLUMN `timeEdited` datetime DEFAULT NULL 
				, MODIFY COLUMN `timeOpened` datetime DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE licenses 
				MODIFY COLUMN `purchaseTime` datetime NOT NULL 
				, MODIFY COLUMN `expireDate` date NOT NULL 
		");
		DB::statement("
			ALTER TABLE licensetypes 
				MODIFY COLUMN `added` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE mass_email_templates 
				MODIFY COLUMN `schedule_time` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE mass_emails 
				MODIFY COLUMN `after_time` datetime DEFAULT NULL 
				, MODIFY COLUMN `opened_at` datetime DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE pushmessages 
				MODIFY COLUMN `time` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE salesmembers 
				MODIFY COLUMN `creationDate` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE statistics_share_info 
				MODIFY COLUMN `customRangeFrom` date DEFAULT NULL 
				, MODIFY COLUMN `customRangeTo` date DEFAULT NULL 
		");
		DB::statement("
			ALTER TABLE todolists 
				MODIFY COLUMN `time` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE transactions 
				MODIFY COLUMN `time` datetime NOT NULL
				, MODIFY COLUMN `nextBillingDate` datetime NOT NULL
		");
		DB::statement("
			ALTER TABLE users 
				MODIFY COLUMN `accountCreationDate` datetime NOT NULL
				, MODIFY COLUMN `lastLogin` datetime NOT NULL
		");
    }
}
