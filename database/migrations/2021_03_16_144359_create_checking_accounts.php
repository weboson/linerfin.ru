<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckingAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checking_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();

            // Base properties
            $table->string('name', 30)->nullable();
            $table->string('num', 30)->nullable();
            $table->float('balance', 20, 2)->default(0);

            // Bank info
            $table->string('bank_name', 55)->nullable();
            $table->string('bank_bik', 25)->nullable();
            $table->string('bank_swift', 25)->nullable();
            $table->string('bank_inn', 25)->nullable();
            $table->string('bank_kpp', 25)->nullable();
            $table->string('bank_correspondent', 40)->nullable();

            $table->string('comment', 300)->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('checking_accounts');
    }
}
