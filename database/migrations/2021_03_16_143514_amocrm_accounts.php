<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmoCRMAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amocrm_accounts', function (Blueprint $table){
            $table->id();
            $table->string('subdomain')->nullable();
            $table->string('client_id')->nullable();
            $table->string('email')->nullable();
            $table->string('hash')->nullable();

            // OAuth
            $table->string('access_token', 1300)->nullable();
            $table->string('refresh_token', 1300)->nullable();
            $table->integer('expires')->nullable();
            $table->string('base_domain', 77)->nullable();

            // PA token
            $table->string('personal_access_token', 300)->nullable();
            $table->foreignId('reference_user_id')->nullable()->constrained('users');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amocrm_accounts');
    }
}
