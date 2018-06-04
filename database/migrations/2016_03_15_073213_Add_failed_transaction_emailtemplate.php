<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFailedTransactionEmailtemplate extends Migration
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
                'id' => 'FAILTRANSACTION',
                'name' => 'Failed Transaction',
                'description' => 'This email is sent to multi user admin when he has failed transaction',
                'from' => 'Sanity OS <donotreply@sanityos.com>',
                'replyTo' => 'Sanity OS <donotreply@sanityos.com>',
                'subject' => 'Failed transaction on ##DATE##',
                'content' => 'Hi ##FIRST_NAME##,<br/><br/>Your ##RECURRING_TYPE## transaction has failed on  ##DATE## and your account has been blocked.
<br/> We will retry it within 24 hrs or renew license as soon as possible to access account. <br/><br/>Thanks<br/>##SITE_NAME##<br/>'
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
