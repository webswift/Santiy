<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('settings');

        Schema::create('settings', function($table) {
            $table->string('name', 100);
            $table->primary('name');

            $table->text('value');
        });

        DB::table('settings')->insert([
            ['name' => 'maxFileUploadSize', 'value' => '3'],
            ['name' => 'maxLandingForm', 'value' => '2'],
            ['name' => 'adminEmail', 'value' => 'contact@sanityos.com'],
            ['name' => 'paypalID', 'value' => 'AaO1YhA9hh0VierUVdULL7agkJJhZGWCr_X05vmGwm9PYWFOozmu2VzaWU1b'],
            ['name' => 'apiSignature', 'value' => 'EBtnwBD9wqo9w0quGjkCZ6awoKRkfDFY7Rwhwv8Bw6CruxZz62cQHDrG4m_N'],
            ['name' => 'supportEmail', 'value' => 'contact@sanityos.com'],
            ['name' => 'defaultCurrency', 'value' => 'USD'],
            ['name' => 'trackingCode', 'value' => 'sdgdsg'],
            ['name' => 'renewalRemainder', 'value' => 'everyWeek'],
            ['name' => 'industry', 'value' => 'Computer Industry'],
            ['name' => 'metaKeywords', 'value' => ''],
            ['name' => 'metaDescription', 'value' => ''],
            ['name' => 'siteName', 'value' => 'Sanity OS'],
            ['name' => 'euroToDollar', 'value' => '0.822728'],
            ['name' => 'gbpToDollar', 'value' => '0.642399'],
            ['name' => 'perMemberDollarPrice', 'value' => '123'],
            ['name' => 'perMemberEuroPrice', 'value' => '102'],
            ['name' => 'perMemberGbpPrice', 'value' => '26'],
            ['name' => '_token', 'value' => 'v7zu5mtZmZeKDurA1sC3uD1RxZYQubIJCZSUIvy0'],
            ['name' => 'invoiceNumber', 'value' => '23'],
            ['name' => 'abandonedCart', 'value' => '1'],
            ['name' => 'adminLoginHtml', 'value' => '<p><strong>Welcome to Bracket Bootstrap 3 Template!</strong></p><ul><li>Fully Responsive Layout</li><li>HTML5\/CSS3 Valid</li><li>Retina Ready</li><li>WYSIWYG CKEditor</li><li>and much more...</li></ul><p><strong>Not a member? <a href="signup.html">Sign Up</a></strong></p>'],
            ['name' => 'loginHtml', 'value' => '<p><strong>Welcome to Bracket Bootstrap 3 Template!</strong></p><ul><li>Fully Responsive Layout</li><li>HTML5\/CSS3 Valid</li><li>Retina Ready</li><li>WYSIWYG CKEditor</li><li>and much more...</li></ul><p><strong>Not a member? <a href="signup.html">Sign Up</a></strong></p>'],
            ['name' => 'frontHtml', 'value' => '<p><strong>Bootstrap 3 Admin Template!</strong></p><p>Bracket is a theme that is perfect if you want to create your own content management, monitoring or any other system for your project.</p><p>Below are some of the benefits you can have when purchasing this template.</p><p>Easy to Customize</p><p>Bracket is made using Bootstrap 3 so you can easily customize any element of this template following the structure of Bootstrap 3.</p><p>Fully Responsive Layout</p><p>Bracket is design to fit on all browser widths and all resolutions on all mobile devices. Try to scale your browser and see the results.</p><p>Retina Ready</p><p>When a user load a page, a script checks each image on the page to see if there&#39;s a high-res version of that image. If a high-res exists, the script will swap that image in place.</p><p>and much more...</p>'],
            ['name' => 'inboundMail', 'value' => '{"from":{"name":"SanityOS","address":"donotreply@sanityos.com"},"replyTo":"donotreply@sanityos.com", "host":"mailtrap.io","port":"2525","username":"3977799d5b006cfcd","password":"9544cca2053b37","requireSSL":null}'],
            ['name' => 'outboundMail', 'value' => '{"from":{"name":"Sanity OS","address":"contact@sanityos.com"},"replyTo":"shalu@froiden.com","host":"auth.smtp.1and1.co.uk","port":"587","username":" ricki@sanityos.com","password":"sos1054","requireSSL":null}'],
            ['name' => 'names', 'value' => '{"firstName":"Phil","surname":"F"}'],
            ['name' => 'baseCurrency', 'value' => 'USD']]
        );
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
