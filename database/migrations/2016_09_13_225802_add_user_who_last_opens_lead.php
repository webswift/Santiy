<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserWhoLastOpensLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
			$table->integer('edited_by_user_id')
				->unsigned()
				->nullable()
				->default(null)
				->comment('weak link to user')
				;
			$table->foreign('edited_by_user_id')
				->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('SET NULL');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
