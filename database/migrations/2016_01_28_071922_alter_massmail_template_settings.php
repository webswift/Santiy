<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMassmailTemplateSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_email_templates', function($table) {
            $table->enum('mail_setting_type', ['Superadmin', 'User'])->default('Superadmin');
            $table->mediumText('mail_settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_email_templates', function($table) {
            $table->dropColumn('mail_setting_type', 'mail_settings');
        });
    }
}
