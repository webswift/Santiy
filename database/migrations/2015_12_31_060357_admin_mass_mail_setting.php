<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminMassMailSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            ['name' => 'massMailServer', 'value' => ''],
            ['name' => 'trialUserLimit', 'value' => ''],
            ['name' => 'singleUserLimit', 'value' => ''],
            ['name' => 'multiUserLimit', 'value' => '']
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
