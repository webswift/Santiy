<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StatisticsShareCustomRange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics_share_info', function($table) {
            $table->date('customRangeFrom')->nullable();
            $table->date('customRangeTo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics_share_info', function($table) {
            $table->dropColumn('customRangeFrom');
            $table->dropColumn('customRangeTo');
        });
    }
}
