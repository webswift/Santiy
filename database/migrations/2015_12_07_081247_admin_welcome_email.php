<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminWelcomeEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('adminemailtemplates')
            ->insert([
                'id' => 'EMAIL_VERIFICATION',
                'name' => 'Email verification email',
                'description' => 'When new trial user created and admin email verification is enabled then this template is used to send verification email',
                'from' => 'Sanity OS <admin@froiden.com>',
                'replyTo' => 'Sanity OS <admin@froiden.com>',
                'subject' => 'Welcome to ##SITE_NAME##',
                'content' => 'Hi ##FIRST_NAME## ##LAST_NAME##,<br/><br/>We wish to say a quick hello and thanks for registering on ##SITE_NAME##!<br/><br/>
<p>Please <a href="##LINK##">click here</a> to verify your email address so that you can login to your respective account</p>
If you did not request this account and feel this is an error, please contact us at ##SUPPORT_EMAIL##.<br/>
<br/>
Thanks<br/>
##SITE_NAME##<br/>',
                'variables' => 'SITE_NAME, FIRST_NAME, LAST_NAME, LINK, SUPPORT_EMAIL'
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
