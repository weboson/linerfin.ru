<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();


            /* --- RELATIONS --- */
            // account
            $table->foreignId('account_id')->nullable()->constrained('accounts')->cascadeOnDelete();

            // VAT type
            $table->foreignId('nds_type_id')->nullable()->constrained('nds_types')->nullOnDelete();

            // used template
            $table->foreignId('template_id')->nullable()->constrained('bills')->nullOnDelete();

            // amoCRM integration
            $table->foreignId('amocrm_account_id')->nullable()->constrained('amocrm_accounts');
            $table->integer('amocrm_lead_id')->nullable();
            $table->integer('amocrm_customer_id')->nullable();


            // Counterparty
            $table->foreignId('counterparty_id')->nullable()->constrained()->nullOnDelete(); // Counterparty
            // Checking account
            $table->foreignId('checking_account_id')->nullable()->constrained('checking_accounts')->nullOnDelete();

            // Attachments
            $table->foreignId('stamp_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('logo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();


            /* --- Base fields --- */
            $table->string('num', 55)->nullable(); // Bill number | template name
            $table->float('sum', 20, 2)->default(0); // Sum of bill
            $table->float('sum_without_vat', 20, 2)->default(0); // Sum of bill
            $table->timestamp('pay_before')->nullable(); // Pay before date


            // Status
            $table->enum('status', ['draft', 'issued', 'rejected', 'template', 'paid', 'realized', 'realized-paid'])->default('draft');
            $table->timestamp('issued_at')->nullable(); // 1
            $table->timestamp('rejected_at')->nullable(); // 0
            $table->timestamp('paid_at')->nullable(); // 2
            $table->timestamp('realized_at')->nullable(); // 3
            // todo: closing account act -> attachment_id


            // Payer contacts
            $table->string('payer_phone', 25)->nullable();
            $table->string('payer_email', 25)->nullable();


            // Signature list options
            $table->boolean('enable_attachments')->default(true);


            // Comment
            $table->string('comment')->nullable();
            $table->string('reject_comment')->nullable();

            // Link and publishing
            $table->string('link', 35)->nullable();
            $table->string('private_key', 35)->nullable();
            $table->enum('access', ['public', 'account'])->default('account');

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
        Schema::dropIfExists('bills');
    }
}
