<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceCorrectHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_correct_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checking_account_id')->constrained('checking_accounts')->cascadeOnDelete();
            $table->float('old_balance', 13, 2)->default(0);
            $table->float('new_balance', 13, 2)->default(0);
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
        Schema::dropIfExists('balance_correct_history');
    }
}
