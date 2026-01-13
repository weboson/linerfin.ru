<?php

namespace App\Console\Commands;

use App\Jobs\CreateTochkaStatementJob;
use App\Models\Account;
use Illuminate\Console\Command;

class UpdateStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:statements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $accounts = Account::get();
        foreach ($accounts as $account) {
            CreateTochkaStatementJob::dispatch($account, now()->startOfMonth(), false, true);
        }
        return 0;
    }
}
