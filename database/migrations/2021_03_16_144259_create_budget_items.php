<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('budget_items')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->enum('category', ['income', 'expense', 'transfer']);
            $table->foreignId('type_id')->nullable()->constrained('budget_item_types')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('budget_item_groups')->nullOnDelete();

            $table->string('name', 55);
            $table->string('comment', 300)->nullable();
            $table->boolean('archived')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE budget_items ADD FULLTEXT search(name, comment)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_items', function($table) {
            $table->dropIndex('search');
        });
        Schema::dropIfExists('budget_item');
    }
}
