<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexSearchByNameValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leadcustomdata', function (Blueprint $table) {
			$table->index(['fieldName', 'value', 'leadID']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leadcustomdata', function (Blueprint $table) {
            $table->dropIndex('leadcustomdata_fieldname_value_leadid_index');
        });
    }
}
