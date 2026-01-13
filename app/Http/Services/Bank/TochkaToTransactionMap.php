<?php

namespace App\Http\Services\Bank;

use App\Models\CheckingAccount;
use App\Models\CounterpartyAccount;
use Carbon\Carbon;

class TochkaToTransactionMap
{
    public static function map($account, $accessToken, $counterparty, $statement, $record, $regular) {
        $checkingAccount = CheckingAccount::where('provider_account_id', $statement['accountId'])->orderBy('created_at', 'desc')->first();
        if (isset($record['DebtorAccount'])) {
            $counterpartyAccount = CounterpartyAccount::where('counterparty_id', $counterparty->id)->where('checking_num', $record['DebtorAccount']['identification'])->first();
            if (!$counterpartyAccount) {
                $counterpartyAccount = CounterpartyAccount::create(['account_id' => $account->id, 'counterparty_id'=> $counterparty->id, 'checking_num' => $record['DebtorAccount']['identification']]);
            }
        }

        if ($regular && (new Carbon($record['documentProcessDate']))->diffInDays(now()) == 0) {
            $date = now()->addHours(-1);
        } else {
            $date = (new Carbon($record['documentProcessDate']))->addHours(12);
        }

        return [
            'external_id' => $record['transactionId'],
            ($record['creditDebitIndicator'] == 'Credit' ? 'to_ca_id' : 'from_ca_id')  => $checkingAccount->id,
//            ($record['creditDebitIndicator'] == 'Credit' ? 'to_ca_balance' : 'from_ca_balance')  => $checkingAccount->id,
            'counterparty_id' => $counterparty->id ?? null,
            'is_active' => $checkingAccount->import_is_active,
            'account_id' => $account->id,
            'amount' => $record['Amount']['amount'],
            'amount_without_vat' => $record['Amount']['amount'],
            'type' => $record['creditDebitIndicator'] == 'Credit' ? 'income' : 'expense',
            'date' => $date,
            'description' => $record['description'],

        ];
    }
}
