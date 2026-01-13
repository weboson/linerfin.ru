<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAmocrmAccountsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amocrm_accounts', function(Blueprint $table){
            $table->integer('amo_user_id')->nullable();
            $table->string('timezone')->nullable();
            $table->string('tariff_name')->nullable();
            $table->integer('users_count')->nullable();
            $table->timestamp('paid_till_date')->nullable();
            $table->timestamp('paid_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('amocrm_accounts', function(Blueprint $table){
            $table->dropColumn('amo_user_id');
            $table->dropColumn('timezone');
            $table->dropColumn('tariff_name');
            $table->dropColumn('users_count');
            $table->dropColumn('paid_till_date');
            $table->dropColumn('paid_from');
        });
    }
}
