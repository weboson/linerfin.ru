<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterpartyAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counterparty_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('counterparty_id')->constrained('counterparties')->cascadeOnDelete();
            $table->string('checking_num', 40);


            // Bank info
            $table->string('bank_name', 55)->nullable();
            $table->string('bank_bik', 25)->nullable();
            $table->string('bank_swift', 25)->nullable();
            $table->string('bank_inn', 25)->nullable();
            $table->string('bank_kpp', 25)->nullable();
            $table->string('bank_correspondent', 40)->nullable();


            // Mark as main
            $table->boolean('main_account')->default(false);
            $table->string('comment', '150')->nullable();

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
        Schema::dropIfExists('counterparty_accounts');
    }
}
