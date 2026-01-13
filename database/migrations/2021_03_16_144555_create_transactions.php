<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('nds_type_id')->nullable()->constrained('nds_types')->nullOnDelete();

            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->float('amount', 20, 2)->default(0);
            $table->float('amount_without_vat', 20, 2)->default(0);
            $table->timestamp('date')->nullable();

            $table->string('description', 700)->nullable();
            $table->foreignId('from_ca_id')->nullable()->constrained('checking_accounts')->nullOnDelete();
            $table->foreignId('to_ca_id')->nullable()->constrained('checking_accounts')->nullOnDelete();
            $table->foreignId('counterparty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('budget_item_id')->nullable()->constrained('budget_items')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            // balance
            $table->float('total_balance', 20, 2)->nullable();
            $table->float('from_ca_balance', 20, 2)->nullable();
            $table->float('to_ca_balance', 20, 2)->nullable();

            // bill's transactions
            $table->foreignId('bill_id')->nullable()->constrained('bills');

            $table->timestamp('made_at')->nullable(); // когда платеж проведен

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
        Schema::dropIfExists('transactions');
    }
}
