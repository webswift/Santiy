<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MassEmailSendingProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('massmailserver_setting', function (Blueprint $table) {
			$table->enum('provider', ['smtp', 'sparkpost', 'sendgrid', 'mandrill'])
				->default('smtp')
				;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('massmailserver_setting', function (Blueprint $table) {
            //
        });
    }
}
