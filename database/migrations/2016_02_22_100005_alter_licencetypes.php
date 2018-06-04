<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLicencetypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licensetypes', function($table) {
            $table->string('discount', 10);
            $table->decimal('priceUSD_year', 10, 2);
            $table->decimal('priceGBP_year', 10, 2);
            $table->decimal('priceEuro_year', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licensetypes', function($table) {
            $table->dropColumn('discount', 'priceUSD_year', 'priceGBP_year', 'priceEuro_year');
        });
    }
}
