<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserPaymentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('user_payment_info', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('currency', ['USD', 'GBP', 'EUR'])->default('USD');
            $table->enum('recurring_type', ['Annually', 'Monthly'])->default('Monthly');
            $table->string('name', 100);
            $table->string('phone_no', 15);
            $table->mediumText('card_details');
            $table->mediumText('billing_details');
            $table->enum('payment_method', ['Card', 'Paypal'])->default('Paypal');
        });

        Schema::table('transactions', function($table) {
            $table->string('merchant_order_no', 100);
            $table->string('order_no', 100);
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        Schema::table('transactions', function($table) {
            $table->dropColumn('merchant_order_no', 'order_no');
        });

        Schema::dropIfExists('user_payment_info');

        DB::commit();
    }
}
