<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UI\Transactions\Calculator;
use App\Http\Services\Bank\CheckingAccountFactory;
use App\Http\Services\Bank\TochkaToCheckingAccountMap;
use App\Http\Services\Bank\TochkaToCounterPartyMap;
use App\Http\Services\Bank\TochkaToTransactionMap;
use App\Jobs\CreateTochkaStatementJob;
use App\Models\Account;
use App\Models\CheckingAccount;
use App\Models\Counterparty;
use App\Models\OAuthAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BankController extends Controller
{

//    public function __construct()
//    {
//        $result = [];
//        $string = explode('?', request()->getRequestUri())[1];
//        parse_str($string,$result);
//        app(Request::class)->merge($result);
//    }

    public function tochkaConnect($state)
    {
        if ($state !== 'demo') {
            $authorizeUrl = app('tochka')->authorize($state);
            return redirect()->away($authorizeUrl);
        } else {
            return view('provider.notsupport');
        }
    }

    public function tochkaCallback(Request $request)
    {

        $account = Account::where('subdomain', \request('state'))->first();

//        if (!$account)
//            return response()->setStatusCode(404);
//        dd(request()->getHttpHost(), $request, $_GET);

        if(request()->getHttpHost() != "linerfinru.test" && $request->get('state') == 'mazurovkonstantin' )
            return redirect()->away('http://linerfinru.test' . request()->getRequestUri());

        $accessToken = app('tochka')->token(\request('code'));
        $oAuthAccount = new OAuthAccount();
        $oAuthAccount->provider = 'tochka';
        $oAuthAccount->access_token = $accessToken->getAccessToken();
        $oAuthAccount->expired_at = now()->addSeconds($accessToken->getExpiresIn());
        $oAuthAccount->expires_in = $accessToken->getExpiresIn();
        $oAuthAccount->refresh_token = $accessToken->getRefreshToken();
        $oAuthAccount->account_id = $account->id;
        $oAuthAccount->save();
        $accessToken = $oAuthAccount->toArray();

        app('tochka')->setAccessToken($accessToken['access_token'], $accessToken['expires_in'], $accessToken['refresh_token']);

        $tochkaAccounts = app('tochka')->account()->all();
        foreach ($tochkaAccounts['Data']['Account'] as $tochkaAccount) {
            $balance = app('tochka')->account($tochkaAccount['accountId'])->balances()['Data']['Balance'][0]['Amount']['amount'];
            $newCheckingAccounts[] = TochkaToCheckingAccountMap::map($tochkaAccount, $balance, $oAuthAccount);
        }

        $checkingAccounts = CheckingAccountFactory::build($account, $newCheckingAccounts, $oAuthAccount);
        return redirect()->route('checking-account.activate', ['id' => $checkingAccounts->pluck('provider_account_id')->toArray()]);
    }

    public function accountActivate()
    {
        $checkingAccounts = CheckingAccount::whereIn('provider_account_id', \request()->get('id'))->get();
        $subdomain = $checkingAccounts->first()->account->subdomain;
        return view('provider.activate', compact('checkingAccounts', 'subdomain'));
    }

    public function testActivate()
    {
//        dd((new Carbon('2023-11-10'))->diffInDays(now()));
        config()->set('queue.default', 'sync');
//        $checkingAccounts = CheckingAccount::whereIn('provider_account_id', \request()->get('id'))->get();
//        dump($checkingAccounts);

        Artisan::call('update:statements');
            die;
        foreach ($checkingAccounts ?? [] as $checkingAccount) {
//            dump($checkingAccount);
            CreateTochkaStatementJob::dispatch($checkingAccount->account);
        }

//        return redirect()->route('checking-account.activate', ['id' => $checkingAccounts->pluck('provider_account_id')->toArray()]);
    }

    public function saveActivate()
    {
        if (!empty(\request()->get('id')))
            $checkingAccounts = CheckingAccount::whereIn('provider_account_id', array_keys(\request()->get('id')))->get();

        foreach ($checkingAccounts ?? [] as $checkingAccount) {
            $checkingAccount->import_is_active = true;
            $checkingAccount->save();
            CreateTochkaStatementJob::dispatch($checkingAccount->account, $checkingAccount->provider_account_created_at);

        }
        return redirect()->to('//' . (\request()->get('subdomain') ?? $checkingAccounts->first()->account->subdomain) . '.' . env('LINERFIN_DOMAIN') . '/settings/banks');
    }

    public function tochkaCustomers()
    {
        $account = Account::where('subdomain', \request('state'))->first();

        $accessTokenModels = OAuthAccount::where('account_id', $account->id)->where('provider', 'tochka')->where('expired_at', '>', now())->get();
        foreach ($accessTokenModels as $accessTokenModel) {
            $accessToken = $accessTokenModel->toArray();

            app('tochka')->setAccessToken($accessToken['access_token'], $accessToken['expires_in'], $accessToken['refresh_token']);
            foreach ($accessTokenModel->checkingAccounts as $checkingAccount) {
                $data = ['Data' => [
                    "Statement" => [
                        'accountId' => $checkingAccount->provider_account_id,
                        'startDateTime' => $checkingAccount->provider_account_created_at->addMonth(-1)->format('Y-m-d'),
                        'endDateTime' => now()->format('Y-m-d'),
                    ],
                ]
                ];

                do {
                    sleep(2);
                    $statements = app('tochka')->statement()->all();

                } while ($statements['Data']["Statement"][0]['status'] !== 'Ready');

                foreach ($statements['Data']['Statement'] as $statement) {

                    foreach (array_reverse($statement['Transaction']) as $transaction) {
                        if (isset($transaction['DebtorParty'])) {
                            $counterparty = Counterparty::where('inn', $transaction['DebtorParty']['inn'])->first();
                            if (!$counterparty) {
                                $counterparty = Counterparty::create(TochkaToCounterPartyMap::map($account, $transaction, $statement));
                            }

                        } else $counterparty = null;

                        $transaction = Transaction::create(TochkaToTransactionMap::map($account, $accessTokenModel, $counterparty ?? null, $statement, $transaction));

                        $now = Carbon::now();
                        $calculator = new Calculator($account);

                        $calculator->calculateTotalBalance($transaction);
                        $calculator->calculateAccountBalances($transaction);


                        /* STEP: Made payment
                        --------------------------------------------*/
                        if ($transaction->date->getPreciseTimestamp(3) <= $now->getPreciseTimestamp(3))
                            $calculator->Core->madePayment($transaction);
                    }
                }
            }
        }
//        $this->recalculateAll($account);
//        dd($statements);
        $tochkaAccounts = app('tochka')->account()->all();
        foreach ($tochkaAccounts['Data']['Account'] as $tochkaAccount) {
            $balance = app('tochka')->account($tochkaAccount['accountId'])->balances()['Data']['Balance'][0]['Amount']['amount'];
            $newCheckingAccounts[] = TochkaToCheckingAccountMap::map($tochkaAccount, $balance);
        }

        $checkingAccounts = CheckingAccountFactory::build($account, $newCheckingAccounts);
        return redirect()->route('checking-account.activate', ['id' => $checkingAccounts->pluck('provider_account_id')->toArray()]);
    }


    public function recalculateAll($account)
    {

//        $this->authorize_account();

//        $account = $this->account;

        // get total balance & create checking_account balances
        $totalBalance = 0;
        $accountsBalance = [];
        $accounts = $account->checkingAccounts;
        foreach ($accounts as $checking_account) {
            $totalBalance += $checking_account->balance;
            $accountsBalance[$checking_account->id] = $checking_account->balance;
        }


        // get transactions | sort by created_at
        $transactions = $account->transactions()->orderByDesc('date');

        // set balance for each transaction
        $transactions->chunk(200, function ($transactions) use ($totalBalance, $accountsBalance) {
            foreach ($transactions as $transaction) {
                $type = $transaction->type;
                $amount = $transaction->amount;
                $transaction->total_balance = $totalBalance;

                switch ($type) {
                    case 'income':
                        $totalBalance -= $amount;
                        if (isset($accountsBalance[$transaction['to_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['to_ca_id']];
                            $accountsBalance[$transaction['to_ca_id']] -= $amount;
                        }
                        $transaction->from_ca_balance = null;
                        break;
                    case 'expense':
                        $totalBalance += $amount;
                        if (isset($accountsBalance[$transaction['from_ca_id']])) {
                            $transaction->from_ca_balance = $accountsBalance[$transaction['from_ca_id']];
                            $accountsBalance[$transaction['from_ca_id']] += $amount;
                        }
                        $transaction->to_ca_balance = null;
                        break;
                    case 'transfer':
                        if (isset($accountsBalance[$transaction['to_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['to_ca_id']];
                            $accountsBalance[$transaction['to_ca_id']] -= $amount;
                        }
                        if (isset($accountsBalance[$transaction['from_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['from_ca_id']];
                            $accountsBalance[$transaction['from_ca_id']] += $amount;
                        }
                        break;
                }

                $transaction->save();
            }
        });
    }

}
