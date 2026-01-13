<?php

namespace App\Jobs;

use App\Http\Controllers\UI\Transactions\Calculator;
use App\Http\Services\Bank\CheckingAccountFactory;
use App\Http\Services\Bank\TochkaToCheckingAccountMap;
use App\Http\Services\Bank\TochkaToCounterPartyMap;
use App\Http\Services\Bank\TochkaToTransactionMap;
use App\Models\Account;
use App\Models\Counterparty;
use App\Models\OAuthAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTochkaStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $account;
    public $startDate;
    public $reverse = true;
    public $regular = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account, $startDate = null, $reverse = true, $regular = false)
    {
        $this->account = $account;
        $this->startDate = $startDate ?? now()->addHours(-1);
        $this->reverse = $reverse ?? !!$startDate;
        $this->regular = $regular;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->statements();
    }


    public function statements()
    {
        $accessTokenModels = OAuthAccount::where('account_id', $this->account->id)->where('provider', 'tochka')->where('expired_at', '>', now())->get();
        foreach ($accessTokenModels as $accessTokenModel) {
            $accessToken = $accessTokenModel->toArray();

            app('tochka')->setAccessToken($accessToken['access_token'], $accessToken['expires_in'], $accessToken['refresh_token']);

            foreach ($accessTokenModel->checkingAccounts as $checkingAccount) {

                $data = [
                    'Data' => [
                        "Statement" => [
                            'accountId' => $checkingAccount->provider_account_id,
                            'startDateTime' => $this->startDate->format('Y-m-d'),
                            'endDateTime' => now()->format('Y-m-d'),
                        ],
                    ]
                ];

                $statement = app('tochka')->statement()->create($data);
                do {
                    sleep(2);
//                    if($this->reverse)
                        $statements = app('tochka')->statement()->all();
//                    else
//                        $statements = app('tochka')->statement($statement['Data']['Statement']['statementId'])->all();

                } while ($statements['Data']["Statement"][0]['status'] !== 'Ready');

                foreach ($statements['Data']['Statement'] as $statement) {
//                    dump($checkingAccount, $statement);
                    $transactions = $this->reverse ? collect($statement['Transaction'])->reverse() : collect($statement['Transaction']);

                    foreach ($transactions as $transaction) {
                        $counterPartySource = $transaction['DebtorParty'] ?? $transaction['CreditorParty'] ?? null;
                        if ($counterPartySource !== null) {
                            $counterparty = Counterparty::where('inn', $counterPartySource['inn'])->first();
                            if (!$counterparty) {
                                $counterparty = Counterparty::create(TochkaToCounterPartyMap::map($this->account, $counterPartySource));
                            }

                        } else $counterparty = null;

                        $transactionModel = Transaction::where('account_id', $this->account->id)->where('external_id', $transaction['transactionId'])->first();
                        if(!$transactionModel) {
                            $transactionModel = Transaction::create(TochkaToTransactionMap::map($this->account, $accessTokenModel, $counterparty ?? null, $statement, $transaction, $this->regular));

//                            $now = Carbon::now();
//                            $calculator = new Calculator($this->account);
//
//                            $calculator->calculateTotalBalance($transactionModel);
//                            $calculator->calculateAccountBalances($transactionModel);
//
//                            if ($transactionModel->date->getPreciseTimestamp(3) <= $now->getPreciseTimestamp(3))
//                                $calculator->Core->madePayment($transactionModel);
                        }
                        $checkingAccount->balance = $statement['endDateBalance'];
                        $checkingAccount->save();
                    }
                }
            }
        }
    }

}
