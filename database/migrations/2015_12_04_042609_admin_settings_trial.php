<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminSettingsTrial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
			['name' => 'trial_cart', 'value' => 'No'],
			['name' => 'trial_period', 'value' => '14'],
			['name' => 'conversion_tracking_code', 'value' => ''],
			['name' => 'email_verification', 'value' => 'No']
        ]);
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
