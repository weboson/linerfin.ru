<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterparties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counterparties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->enum('type', ['LEGAL', 'INDIVIDUAL'])->default('LEGAL');
            $table->foreignId('category_id')->nullable()->constrained('counterparty_categories')->nullOnDelete();
            $table->foreignId('opf_type_id')->nullable()->constrained('opf_types')->nullOnDelete();
            $table->string('inn')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('address', 150)->nullable();
            $table->string('legal_address', 150)->nullable();

            $table->string('comment', 150)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE counterparties ADD FULLTEXT search(name, inn, kpp)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('counterparties', function($table) {
            $table->dropIndex('search');
        });
        Schema::dropIfExists('counterparties');
    }
}
