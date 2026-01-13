<?php

namespace App\Http\Controllers\UI\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Calculator extends \App\Http\Abstraction\AccountAbstract
{

    protected $middlewareAuthorize = false;
    protected $constructAuthorize = true;

    public $Core;

    public function __construct(Account $account = null)
    {
        parent::__construct($account);
        $this->Core = new CalculatorCore($this->account ?: null);
    }


    public function calculateTotalBalance(Transaction $transaction){


        /* STEP: Get transaction data
        -------------------------------------------*/
            $date = $transaction->date;
            $amount = CalculatorCore::getAmountDirection($transaction);


        /* STEP: Update total balance current transaction
        ---------------------------------------------------*/
            $prev_balance = $this->Core->getLastTotalBalanceByTransaction($transaction);
            $total_balance = $prev_balance + $amount;
            $transaction->update(compact('total_balance'));

            // if transfer type -> total_balance doesn't change
            if($amount === 0) {
//                echo 'Для переводов перерасчет общего баланса не требуется';
                return;
            }

        /* STEP: Get affected chains
        -------------------------------------------*/
            $affected_chains = Transaction::where('account_id', $this->account_id)
                ->where('id', '<>', $transaction->id)
                ->where('date', '>=', $date->toDateTimeString())
                ->orderBy('date');


            $affected_chains->increment('total_balance', $amount);

    }





    public function calculateAccountBalances(Transaction $transaction){


        /* STEP: Get transaction data
        -------------------------------------------*/
            $date = $transaction->date;
            $amount = $transaction->amount;


        /* STEP: Update account balances current transaction
        ---------------------------------------------------*/

            // expense bank
            if($from_bank = $transaction->fromCheckingAccount){
                $prev_balance = $this->Core->getLastAccountBalanceByDate($from_bank, $date, [['id', '<>', $transaction->id]]);
                $transaction->update(['from_ca_balance' => $prev_balance - $amount]);
            }
            // income bank
            if($to_bank = $transaction->toCheckingAccount){
                $prev_balance = $this->Core->getLastAccountBalanceByDate($to_bank, $date, [['id', '<>', $transaction->id]]);
                $transaction->update(['to_ca_balance' => $prev_balance + $amount]);
            }


        /* STEP: Recalculate affected transactions
        --------------------------------------------*/
            if($from_bank){
                Transaction::where('account_id', $this->account_id)
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('from_ca_id', $from_bank->id)
                    ->decrement('from_ca_balance', $amount);

                Transaction::where('account_id', $this->account_id)
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('to_ca_id', $from_bank->id)
                    ->decrement('to_ca_balance', $amount);
            }

            if($to_bank){
                Transaction::where('account_id', $this->account_id)
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('from_ca_id', $to_bank->id)
                    ->increment('from_ca_balance', $amount);

                Transaction::where('account_id', $this->account_id)
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('to_ca_id', $to_bank->id)
                    ->increment('to_ca_balance', $amount);
            }

    }




    public function recalculateAll(){

        $this->authorize_account();

        $account = $this->account;

        // get total balance & create checking_account balances
        $totalBalance = 0;
        $accountsBalance = [];
        $accounts = $account->checkingAccounts;
        foreach ($accounts as $checking_account){
            $totalBalance += $checking_account->balance;
            $accountsBalance[$checking_account->id] = $checking_account->balance;
        }


        // get transactions | sort by created_at
        $transactions = $account->transactions()->orderByDesc('date');

        // set balance for each transaction
        $transactions->chunk(200, function($transactions) use ($totalBalance, $accountsBalance){
            foreach($transactions as $transaction){
                $type = $transaction->type;
                $amount = $transaction->amount;
                $transaction->total_balance = $totalBalance;

                switch ($type){
                    case 'income':
                        $totalBalance -= $amount;
                        if(isset($accountsBalance[$transaction['to_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['to_ca_id']];
                            $accountsBalance[$transaction['to_ca_id']] -= $amount;
                        }
                        $transaction->from_ca_balance = null;
                        break;
                    case 'expense':
                        $totalBalance += $amount;
                        if(isset($accountsBalance[$transaction['from_ca_id']])) {
                            $transaction->from_ca_balance = $accountsBalance[$transaction['from_ca_id']];
                            $accountsBalance[$transaction['from_ca_id']] += $amount;
                        }
                        $transaction->to_ca_balance = null;
                        break;
                    case 'transfer':
                        if(isset($accountsBalance[$transaction['to_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['to_ca_id']];
                            $accountsBalance[$transaction['to_ca_id']] -= $amount;
                        }
                        if(isset($accountsBalance[$transaction['from_ca_id']])) {
                            $transaction->to_ca_balance = $accountsBalance[$transaction['from_ca_id']];
                            $accountsBalance[$transaction['from_ca_id']] += $amount;
                        }
                        break;
                }

                $transaction->save();
            }
        });
    }



    /**
     * Проведение платежей (вероятно всего запуск с CRON)
     * @return JsonResource
     */
    public function madePayments(){
        $now = new Carbon();
        $builder = Transaction::whereNull('made_at')->where('date', '<=', $now);
        $builder->chunk(200, function($transactions){
            foreach($transactions as $transaction)
                $this->Core->madePayment($transaction);
        });

        return new JsonResource(['success' => true]);
    }




    /*
     * Типы изменений [учитывать порядок выполнения]
     * 1. Изменение даты назад
     * Транзакция получает расчет по балансу в точке новой даты
     * Все транзакции от новой даты до предыдущей пересчитываются с учетом суммы
     *
     * 2. Изменение даты вперед
     * Все транзакции от предыдущей даты до новой пересчитываются с учетом суммы
     * Транзакция получает расчет по балансу в точке новой даты
     *
     * 3. Изменение суммы
     * Все транзакции начиная от текущей даты (вперед) пересчитываются с учетом разницы изменения суммы
     *
     * 4. Изменение счета
     * Если платеж проведен необходимо выполнить обратный перерасчет для предыдущего счета и пересчитать новый
     * У всех транзакций от текущего времени с использованием предыдущего счета необходимо сделать перерасчет
     * Аналогично для транзакций нового выбранного счета
     *
     *
     */


    /**
     * Process transaction amount changes
     * Обработка изменений суммы транзакции
     * @param Transaction $transaction
     * @param float|int $oldAmount
     */
    public function processAmountChanges(Transaction $transaction, float $oldAmount = 0){

        /*
         * Все транзакции начиная от текущей даты (вперед) пересчитываются с учетом разницы изменения суммы
         */

        $date = $transaction->date;

        $difference = $transaction->amount - $oldAmount;
        $amountDirection = $transaction->type === 'income' ? 1 : -1;


        /* Update bank balances
        --------------------------------------------*/
            if($transaction->made_at){
                if($transaction->fromCheckingAccount)
                    $transaction->fromCheckingAccount->decrement('balance', $difference);
                if($transaction->toCheckingAccount)
                    $transaction->toCheckingAccount->increment('balance', $difference);
            }



        /* Update balances of current transaction
        --------------------------------------------*/
            $transaction->increment('total_balance', $difference * $amountDirection);
            if($transaction->from_ca_id)
                $transaction->increment('from_ca_balance', $difference * -1);
            if($transaction->to_ca_id)
                $transaction->increment('to_ca_balance', $difference);


        /* Total balance Shift
        -----------------------------------*/
            $transactions = Transaction::where('account_id', $this->account_id)
                ->where('date', '>=', $date->toDateTimeString())
                ->where('id', '!=', $transaction->id);

            $transactions->increment('total_balance', $difference * $amountDirection);


        /* Bank balances shift
        -----------------------------------*/
            // for expense banks
            if($from_ca_id = $transaction->from_ca_id){
                $this->account->transactions()
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('from_ca_id', $from_ca_id)
                    ->increment('from_ca_balance', $difference * -1);

                $this->account->transactions()
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('to_ca_id', $from_ca_id)
                    ->increment('to_ca_balance', $difference * -1);
            }

            // for income banks
            if($to_ca_id = $transaction->to_ca_id){
                $this->account->transactions()
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('from_ca_id', $to_ca_id)
                    ->increment('from_ca_balance', $difference);

                $this->account->transactions()
                    ->where('date', '>=', $date->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->where('to_ca_id', $to_ca_id)
                    ->increment('to_ca_balance', $difference);
            }
    }


    public function processDateChanges(Transaction $transaction, Carbon $oldDate = null){

        // get dates
        $newDate = $transaction->date;
        $newDateTimestamp = $newDate->getPreciseTimestamp(3);
        $oldDateTimestamp = $oldDate->getPreciseTimestamp(3);

        // get amount
        $amount = $transaction->amount;
        switch ($transaction->type){
            case 'transfer':
                $amountDirection = 0;
                break;
            case 'expense':
                $amountDirection = -1;
                break;
            default:
                $amountDirection = 1;
        }

        $DateToBack = $oldDate->getPreciseTimestamp(3) > $newDate->getPreciseTimestamp(3);


        if($newDateTimestamp === $oldDateTimestamp) return; // needless


        /* STEP: Recalculate total balances
        --------------------------------------------*/

            // Shift back
            if($DateToBack){

                // update transaction total balance
                $totalBalance = $this->Core->getLastTotalBalanceByDate($newDate, [['id', '!=', $transaction->id]]);
                $transaction->update([
                    'total_balance' => $totalBalance + $amount * $amountDirection
                ]);


                // update total balance
                $this->account->transactions()->where('date', '>=', $newDate->toDateTimeString())
                    ->where('date', '<=', $oldDate->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->increment('total_balance', $amount * $amountDirection);
            }

            // Shift front
            else{
                // update total balance
                $this->account->transactions()
                    ->where('date', '>=', $oldDate->toDateTimeString())
                    ->where('date', '<=', $newDate->toDateTimeString())
                    ->where('id', '!=', $transaction->id)
                    ->decrement('total_balance', $amount * $amountDirection);

                // update transaction total balance
                $totalBalance = $this->Core->getLastTotalBalanceByDate($newDate, [['id', '!=', $transaction->id]]);
                $transaction->update([
                    'total_balance' => $totalBalance + $amount * $amountDirection
                ]);

            }


        /* STEP: Recalculate bank balances
        --------------------------------------------*/
            $expenseBank = $transaction->from_ca_id;
            $incomeBank = $transaction->to_ca_id;

            $bankStack = compact('expenseBank', 'incomeBank');

            if($DateToBack) // before recalculate
                $this->processDateChanges__updateBalance($transaction);

            // update balances of affected chains
            foreach($bankStack as $type => $bank_id){
                $amountBankDirection = $type === 'expenseBank' ? -1 : 1;

                $this->account->transactions()
                    ->where('id', '!=', $transaction->id)
                    ->where('date', $DateToBack ? '>=' : '<=', $newDate->toDateTimeString())
                    ->where('date', $DateToBack ? '<=' : '>=' , $oldDate->toDateTimeString())
                    ->where('from_ca_id', $bank_id)
                    ->decrement('from_ca_balance', $amount * $amountBankDirection * ($DateToBack ? 1 : -1));


                $this->account->transactions()
                    ->where('id', '!=', $transaction->id)
                    ->where('date', $DateToBack ? '>=' : '<=', $newDate->toDateTimeString())
                    ->where('date', $DateToBack ? '<=' : '>=' , $oldDate->toDateTimeString())
                    ->where('to_ca_id', $bank_id)
                    ->increment('to_ca_balance', $amount * $amountBankDirection * ($DateToBack ? 1 : -1));
            }

            if(!$DateToBack) // after recalculate
                $this->processDateChanges__updateBalance($transaction);
    }

    protected function processDateChanges__updateBalance(Transaction $transaction){
        $amount = $transaction->amount;
        $date = $transaction->date;

        if($expenseBank = $transaction->fromCheckingAccount){
            $balance = $this->Core->getLastAccountBalanceByDate($expenseBank, $date, [['id', '!=', $transaction->id]]);
            $transaction->update(['from_ca_balance' => $balance - $amount]);
        }
        if($incomeBank = $transaction->toCheckingAccount){
            $balance = $this->Core->getLastAccountBalanceByDate($incomeBank, $date, [['id', '!=', $transaction->id]]);
            $transaction->update(['to_ca_balance' => $balance + $amount]);
        }
    }



    // [to remove]
    public function processBankChanges(Transaction $transaction){
        /*$this->rollbackCalculateBalances($transaction);
        $this->calculateTotalBalance($transaction);
        $this->calculateAccountBalances($transaction);*/
    }



    public function rollbackCalculateBalances(Transaction $transaction){

        $type = $transaction->type;
        $date = $transaction->date;
        $amount = $transaction->amount;


        /* STEP: Rollback total balance
        --------------------------------------------*/
            if($type !== "transfer"){
                $amountDirection = CalculatorCore::getAmountDirection($transaction);

                $this->account->transactions()->where([
                    ['date', '>=', $date->toDateTimeString()],
                    ['id', '!=', $transaction->id]
                ])->decrement("total_balance", $amountDirection);
            }


        /* STEP: Rollback account balances
        --------------------------------------------*/

            // for expense bank
            if($expenseBank = $transaction->fromCheckingAccount){
                $this->account->transactions()->where([
                    ['date', '>=', $date->toDateTimeString()],
                    ['id', '!=', $transaction->id],
                    ['from_ca_id', '=', $expenseBank->id]
                ])->increment('from_ca_balance', $amount);

                $this->account->transactions()->where([
                    ['date', '>=', $date->toDateTimeString()],
                    ['id', '!=', $transaction->id],
                    ['to_ca_id', '=', $expenseBank->id]
                ])->increment('to_ca_balance', $amount);
            }

            // for income bank
            if($incomeBank = $transaction->toCheckingAccount){
                $this->account->transactions()->where([
                    ['date', '>=', $date->toDateTimeString()],
                    ['id', '!=', $transaction->id],
                    ['from_ca_id', '=', $incomeBank->id]
                ])->decrement('from_ca_balance', $amount);

                $this->account->transactions()->where([
                    ['date', '>=', $date->toDateTimeString()],
                    ['id', '!=', $transaction->id],
                    ['to_ca_id', '=', $incomeBank->id]
                ])->decrement('to_ca_balance', $amount);
            }

        /* STEP: Update current transaction balances
        --------------------------------------------*/
            $transaction->update([
                'total_balance' => null,
                'from_ca_balance' => null,
                'to_ca_balance' => null
            ]);
    }
}
