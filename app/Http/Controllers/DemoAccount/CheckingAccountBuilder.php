<?php


namespace App\Http\Controllers\DemoAccount;


class CheckingAccountBuilder extends BuilderAbstract
{

    public function build($accounts = null)
    {
        $accounts = $accounts ?? [
            ['name' => 'Расчетный счет 1', 'num' => '5535 2342 1123 2342', 'balance' => '50000', 'bank_name' => 'Сбербанк', 'bank_bik' => '23456453'],
            ['name' => 'Расчетный счет 2', 'num' => '1442 3343 6556 0001', 'balance' => '21000', 'bank_name' => 'Сбербанк', 'bank_bik' => '23456453'],
        ];

        foreach($accounts as $account)
            $this->account->checkingAccounts()->create($account);
    }
}
