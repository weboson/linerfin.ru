<?php


namespace App\Http\Controllers\UI\Transactions;


use App\Http\Abstraction\AccountAbstract;
use App\Models\Account;
use App\Models\CheckingAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculatorCore extends AccountAbstract
{
    protected $middlewareAuthorize = false;
    protected $constructAuthorize = true;


    public function __construct(Account $account = null)
    {
        if($account){
            $this->account = $account;
            $this->user = $account->user;
        }
        parent::__construct();
    }


    public function madePayment(Transaction $transaction){
        if($transaction->made_at)
            return;

        DB::transaction(function() use ($transaction){

            $amount = $transaction->amount;
            if($transaction->fromCheckingAccount){
                $transaction->fromCheckingAccount->balance -= $amount;
                $transaction->fromCheckingAccount->save();
            }
            if($transaction->toCheckingAccount){
                $transaction->toCheckingAccount->balance += $amount;
                $transaction->toCheckingAccount->save();
            }
            $transaction->update(['made_at' => new Carbon]);
        });
    }

    public function rollbackPayment(Transaction $transaction){
        if(!$transaction->made_at)
            return;

        DB::transaction(function() use ($transaction){
            $amount = $transaction->amount;
            if($transaction->fromCheckingAccount){
                $transaction->fromCheckingAccount->balance += $amount;
                $transaction->fromCheckingAccount->save();
            }
            if($transaction->toCheckingAccount){
                $transaction->toCheckingAccount->balance -= $amount;
                $transaction->toCheckingAccount->save();
            }
            $transaction->update(['made_at' => null]);
        });
    }



    public function getLastTotalBalanceByTransaction(Transaction $transaction){
        return $this->getLastTotalBalanceByDate($transaction->date, [['id', '<>', $transaction->id]]);
    }

    public function getLastTotalBalanceByDate(Carbon $date, $where = null){


        // 1. get prev transaction
        $prev_transaction = Transaction::where('account_id', $this->account_id)
            ->where('date', '<=', $date->toDateTimeString())
            ->whereNotNull('total_balance');

        // custom conditions
        if(isset($where)) $prev_transaction->where($where);

        $prev_transaction = $prev_transaction->orderByDesc('date')->first();

        if($prev_transaction)
            return $prev_transaction->total_balance;


        // 2. get next transaction
        $next_transaction = Transaction::where('account_id', $this->account_id)
            ->where('date', '>=', $date->toDateTimeString())
            ->whereNotNull('total_balance');

        // custom conditions
        if(isset($where)) $next_transaction->where($where);

        $next_transaction = $next_transaction->orderBy('date')->first();

        if($next_transaction){
            if ($next_transaction->type == 'transfer')
                return $next_transaction->total_balance;

            return $next_transaction->total_balance - self::getAmountDirection($next_transaction);
        }


        // 3. get total balance now
        $totalBalance = $this->getTotalBalance();
        if(!empty($excludeID) && $transaction = Transaction::find($excludeID)){
            $type = $transaction->type;
            $amount = $transaction->amount;
            if($transaction->made_at && $type !== 'transfer') {
                if($type === 'income')
                    $amount *= -1;
                $totalBalance += $amount;
            }
        }

        return $totalBalance;
    }





    /**
     * Get last balance of bank account
     * using date-point and custom conditions
     * ----------------------------------------
     * Получение последнего баланса банка
     * используя временную метку и выбранные условия
     * @param CheckingAccount $bank
     * @param Carbon $date
     * @param null $where
     * @return float|int|mixed
     */
    public function getLastAccountBalanceByDate(CheckingAccount $bank, Carbon $date, $where = null){

        $bank_id = $bank->id;

        /* STEP: 1. get prev transaction
        --------------------------------------------*/
            // by account and date before
            $prev_transaction = Transaction::where('account_id', $this->account_id)
                ->where('date', '<=', $date->toDateTimeString());

            // custom conditions
            if(isset($where)) $prev_transaction->where($where);

            // bank relation
            $prev_transaction->where(function($query) use ($bank_id){
                $query->where('from_ca_id', $bank_id)
                    ->orWhere('to_ca_id', $bank_id);
            });

            // order DESC and get
            $prev_transaction = $prev_transaction->orderByDesc('date')->first();

            // return balance
            if($prev_transaction){
                if($prev_transaction->from_ca_id == $bank_id)
                    return $prev_transaction->from_ca_balance;
                else
                    return $prev_transaction->to_ca_balance;
            }


        //    [if not found]
        /* STEP: 2. get next transaction
        --------------------------------------------*/
            // by account and date after
            $next_transaction = Transaction::where('account_id', $this->account_id)
                ->where('date', '>=', $date->toDateTimeString());

            // custom conditions
            if(isset($where)) $next_transaction->where($where);


            // bank relation
            $next_transaction->where(function($query) use ($bank_id){
                $query->where('from_ca_id', $bank_id)
                    ->orWhere('to_ca_id', $bank_id);
            });

            // order ASC and get
            $next_transaction = $next_transaction->orderBy('date')->first();

            // return balance
            if($next_transaction){
                if($next_transaction->from_ca_id == $bank_id)
                    return $next_transaction->from_ca_balance + $next_transaction->amount;
                else
                    return $next_transaction->to_ca_balance - $next_transaction->amount;
            }


        /* STEP: 3. get account balance now
        --------------------------------------------*/
            return $bank->balance;
    }


    /**
     * Get total balance of account
     * ----------------------------
     * Получить общий баланс текущего аккаунта
     * @return double
     */
    public function getTotalBalance(){
        $totalBalance = 0;
        $accounts = $this->account->checkingAccounts;
        foreach ($accounts as $checking_account)
            $totalBalance += $checking_account->balance;

        return $totalBalance;
    }



    /**
     * Get transaction amount with direction
     * ---------------------------------------
     * Получение суммы операции с учетом направления (+/-)
     * @param Transaction $transaction
     * @return float|int|mixed
     */
    public static function getAmountDirection(Transaction $transaction){
        switch ($transaction->type){
            case "expense":
                return $transaction->amount * -1;
        }
        return $transaction->amount;
    }

}
