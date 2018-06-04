<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionurlBounceDeliveredStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_emails', function($table) {
            $table->enum('aws_status', ['Bounce', 'Complaint', 'Delivery'])->default('Delivery');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('company_name', 100)->nullable();
        });

        Schema::create('aws_subscription_url', function($table) {
            $table->increments('id');
            $table->string('topic_arn', 255);
            $table->string('url', 2000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_emails', function($table) {
            $table->dropColumn('aws_status', 'first_name', 'last_name', 'company_name');
        });

        Schema::dropIfExists('aws_subscription_url');
    }
}
