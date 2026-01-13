<?php

namespace App\Http\Services\Bank;

use App\Models\CheckingAccount;

class CheckingAccountFactory
{
    public static function build($account, $checkingAccounts = [],  $oAuthAccount = null)
    {
        $newCheckingAccounts = [];
        foreach($checkingAccounts as $checkingAccount) {
            $cha = CheckingAccount::where('provider_account_id', $checkingAccount['provider_account_id'])->first();
            if(!$cha) {
                $newCheckingAccounts[] = $account->checkingAccounts()->create($checkingAccount);
            } else {
                $cha->o_auth_account_id =  $oAuthAccount->id;
                $cha->save();
                $newCheckingAccounts[] = $cha;
            }
        }
        return collect($newCheckingAccounts);
    }
}
