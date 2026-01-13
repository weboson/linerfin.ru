<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCheckingAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checking_accounts', function (Blueprint $table) {
            $table->string('provider')->nullable();
            $table->integer('o_auth_account_id')->nullable()->index();
            $table->string('provider_account_id')->nullable();
            $table->string('import_is_active')->default(false);
            $table->timestamp('provider_account_updated_at')->nullable();
            $table->timestamp('provider_account_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checking_accounts', function (Blueprint $table) {
            $table->dropColumn(['provider', 'o_auth_account_id', 'provider_account_id', 'import_is_active', 'provider_account_updated_at', 'provider_account_created_at']);
        });
    }
}
