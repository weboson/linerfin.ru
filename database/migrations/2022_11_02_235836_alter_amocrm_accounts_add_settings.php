<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAmocrmAccountsAddSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amocrm_accounts', function(Blueprint $table){
            $table->boolean('bill_closing')->default(false);
            $table->boolean('task_creating')->default(false);
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
            $table->dropColumn('bill_closing');
            $table->dropColumn('task_creating');
        });
    }
}
