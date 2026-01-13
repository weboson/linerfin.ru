<?php

namespace App\Http\Controllers\UI;

use App\Http\Abstraction\AccountAbstract;
use App\Models\CheckingAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BalanceController extends AccountAbstract
{
    public function getBalance(){

        // current month timestamp
        $monthPeriod = [
            date('Y-m-01T00:00:00'),
            date('Y-m-tT23:59:59'),
        ];

        // Get incomes
        $incomes = Transaction::where('type', 'income')
            ->where('account_id', $this->account_id)
            ->where('created_at', '>=', $monthPeriod[0])
            ->where('created_at', '<=', $monthPeriod[1])->sum('amount');

        // Get expenses
        $expenses = Transaction::where('type', 'expense')
            ->where('account_id', $this->account_id)
            ->where('created_at', '>=', $monthPeriod[0])
            ->where('created_at', '<=', $monthPeriod[1])->sum('amount');

        // Get result balance
        $balance = $incomes - $expenses;

        // Get total balance
        $total = CheckingAccount::where('account_id', $this->account_id)->sum('balance');

        return compact('incomes', 'expenses', 'balance', 'total');
    }

    public function getCheckingAccounts(){
        $checking_accounts = CheckingAccount::where('account_id', $this->account_id)->get()->toArray();
        return compact('checking_accounts');
    }
}
