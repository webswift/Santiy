<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationToLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_emails', function (Blueprint $table) {
			$table->integer('lead_id')
				->unsigned()
				->nullable()
				->default(null)
				->after('id')
				->comment('weak link on lead')
				;
			$table->foreign('lead_id')
				->references('id')->on('leads')
                ->onUpdate('cascade')
                ->onDelete('SET NULL');

			$table->index(['template_id', 'lead_id']);
        });

		DB::statement("
			UPDATE mass_emails, leadcustomdata, mass_email_templates, leads
			SET mass_emails.lead_id = leads.id
			WHERE mass_emails.template_id = mass_email_templates.id
				AND mass_email_templates.campaign_id = leads.campaignID
				AND leadcustomdata.leadID = leads.id
				AND leadcustomdata.fieldName = 'Email' 
				AND leadcustomdata.value = mass_emails.email
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_emails', function (Blueprint $table) {
            //
        });
    }
}
