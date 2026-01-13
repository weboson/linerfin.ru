<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // relations
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('opf_type_id')->nullable()->constrained('opf_types')->nullOnDelete();
            $table->foreignId('nds_type_id')->nullable()->constrained('nds_types')->nullOnDelete();
            $table->foreignId('taxation_system_id')->nullable()->constrained('taxation_systems')->nullOnDelete();

            $table->enum('type', ['LEGAL', 'INDIVIDUAL'])->default('LEGAL');

            // main data
            $table->string('subdomain')->unique();
            $table->string('name', 100);
            $table->string('inn', 50)->nullable();
            $table->string('kpp', 50)->nullable();
            $table->string('ogrn', 50)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('legal_address', 150)->nullable();
            $table->string('director_position', 55)->nullable();
            $table->string('director_name', 55)->nullable();
            $table->string('accountant_position', 55)->nullable();
            $table->string('accountant_name', 55)->nullable();

            // company attachments
            $table->foreignId('director_signature_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('accountant_signature_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('logo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('stamp_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();

            // demo mod
            $table->boolean('is_demo')->default(false);

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
        Schema::dropIfExists('accounts');
    }
}
