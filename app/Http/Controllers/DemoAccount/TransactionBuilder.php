<?php


namespace App\Http\Controllers\DemoAccount;


use App\Http\Controllers\UI\Transactions\Calculator;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TransactionBuilder extends BuilderAbstract
{


    public function build()
    {
        $this->Calc = new Calculator($this->account);

        for($i = 0; $i <= 100; $i++){
            $transaction = new Transaction();
            $transaction->account()->associate($this->account);
            $this->generateAmount($transaction)
                ->generateType($transaction)
                ->generateBudgetItem($transaction)
                ->generateProject($transaction)
                ->generateCheckingAccount($transaction)
                ->generateDate($transaction);

            $transaction->save();
            $this->calculate($transaction);
        }

    }

    public function generateAmount(Transaction $transaction){
        $transaction->amount = mt_rand(1000, 34999);
        return $this;
    }


    protected function generateType(Transaction $transaction){
        $transaction->type = Arr::random(['income', 'income', 'income', 'income', 'expense']); //   1/4
        return $this;
    }

    protected function generateProject(Transaction $transaction){
        if(mt_rand(0, 3) === 0) return $this;
        $project = $this->account->projects()->inRandomOrder()->first();
        if($project)
            $transaction->project()->associate($project);
        return $this;
    }

    protected function generateBudgetItem(Transaction $transaction){
        if(mt_rand(0, 3) === 0) return $this;
        $item = $this->account->budgetItems()->inRandomOrder()->first();
        if($item)
            $transaction->budgetItem()->associate($item);
        return $this;
    }

    protected function generateCheckingAccount(Transaction $transaction){
        $account = $this->account->checkingAccounts()
            ->whereNotNull('num')
            ->inRandomOrder()
            ->first();

        if($account){
            switch ($transaction->type){
                case 'income':
                    $transaction->toCheckingAccount()->associate($account);
                    break;
                case 'expense':
                    $transaction->fromCheckingAccount()->associate($account);

            }
        }
        return $this;
    }

    protected function generateDate(Transaction $transaction){
        $carbon = new Carbon();
        $carbon->subDays(mt_rand(1, 90));
        $transaction->date = $carbon;

        return $this;
    }


    /** @var Calculator */
    protected $Calc;

    protected function calculate(Transaction $transaction){
        $this->Calc->calculateTotalBalance($transaction);
        $this->Calc->calculateAccountBalances($transaction);
        $this->Calc->Core->madePayment($transaction);
    }
}
