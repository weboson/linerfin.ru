<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('counterparty_id')->constrained('counterparties')->cascadeOnDelete(); // base of parent
            $table->string('name', 40);
            $table->string('surname', 40)->nullable();
            $table->string('patronymic', 40)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 40)->nullable();
            $table->boolean('main_contact')->default(false); // todo
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
        Schema::dropIfExists('contacts');
    }
}
