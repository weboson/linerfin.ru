<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('name', 55);
            $table->string('comment', 300)->nullable();
            $table->boolean('archived')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE projects ADD FULLTEXT search(name,comment)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function($table) {
            $table->dropIndex('search');
        });
        Schema::dropIfExists('projects');
    }
}
