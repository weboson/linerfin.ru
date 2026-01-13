<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetItemTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_item_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('desc', 500)->nullable();
            $table->enum('type', ['operation', 'investment', 'financial', 'cost'])
                    ->comment("Виды деятельности - операционная, инвестиционная и финансовая. cost - себестоимость");
            $table->enum('category', ['income', 'expense', 'transfer']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_item_types');
    }
}
