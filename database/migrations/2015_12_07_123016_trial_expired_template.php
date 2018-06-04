<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrialExpiredTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('adminemailtemplates')
          ->insert([[
              'id' => 'TRIALEXPIRED',
              'name' => 'Trial Period Expired',
              'description' => 'This template is used when trial period of user is expired',
              'from' => 'Sanity OS <donotreply@sanityos.com>',
              'replyTo' => 'Sanity OS <donotreply@sanityos.com>',
              'subject' => 'Welcome to ##SITE_NAME##',
              'content' => 'Hi ##FIRST_NAME##,<br/>
<br/>
Your trial period expired on  ##EXPIRY## and your account is no longer active.<br/>
Please buy license as soon as possible to access account. <br/>

<br/>
Thanks<br/>
##SITE_NAME##<br/>',
              'variables' => 'SITE_NAME, FIRST_NAME, EXPIRY'
          ],
              [
                  'id' => 'TRIALTOEXPIRE',
                  'name' => 'Trial Period about to Expire',
                  'description' => 'This template is used when trial period of user is to be expired',
                  'from' => 'Sanity OS <admin@froiden.com>',
                  'replyTo' => 'Sanity OS <admin@froiden.com>',
                  'subject' => 'Welcome to ##SITE_NAME##',
                  'content' => 'Hi ##FIRST_NAME##,<br/>
<br/>
Your trial license is going to expire on  ##EXPIRY##.<br/>
Please buy new license as soon as possible otherwise your account will be blocked.
<br/>

<br/>
Thanks<br/>
##SITE_NAME##<br/>',
                  'variables' => 'SITE_NAME, FIRST_NAME, EXPIRY'
              ]]);

	    DB::table('settings')->insert(['name' => 'trialRenewalRemainder', 'value' => 'everyDay']);
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
