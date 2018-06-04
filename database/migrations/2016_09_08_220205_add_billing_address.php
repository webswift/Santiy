<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_payment_info', function (Blueprint $table) {
            $table->string('billing_email', 255);
        });
		DB::statement("
			UPDATE user_payment_info, users
			SET user_payment_info.billing_email = users.email
			WHERE users.id = user_payment_info.user_id
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_payment_info', function (Blueprint $table) {
            //
        });
    }
}
