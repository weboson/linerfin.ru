<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable(); // for url

            // Attachment customization
            $table->string('type', 69)->default('attachment'); // type of attachment
            $table->jsonb('meta')->nullable(); // JSON meta params

            // relations
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('account_id')->nullable();

            // file params
            $table->string('path');
            $table->string('name');
            $table->string('extension')->nullable();
            $table->float('size')->nullable();

            // access
            $table->boolean('public')->default(false);
            $table->boolean('account_public')->default(false);

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
        Schema::dropIfExists('attachments');
    }
}
