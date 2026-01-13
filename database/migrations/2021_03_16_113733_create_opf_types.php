<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOPFTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opf_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('short_name', 50)->nullable();
            $table->string('type', 80)->nullable();
            $table->integer('code')->nullable();
            $table->boolean('for_individual')->nullable();
            $table->boolean('for_legal')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opf_types');
    }
}
