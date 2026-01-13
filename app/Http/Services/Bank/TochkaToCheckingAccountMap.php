<?php

namespace App\Http\Services\Bank;

use Carbon\Carbon;

class TochkaToCheckingAccountMap
{
    public static function map($record, $balance = 0, $oAuthAccount= null) {
        return [
            'name' => $record['accountDetails'][0]['name'],
            'num' => explode('/',$record['accountId'])[0],
            'balance' => 0,
            'bank_name' => 'ООО «Банк Точка»',
            'bank_bik' => '044525104',
            'bank_inn' => '9721194461',
            'bank_kpp' => '772101001',
            'bank_correspondent' => '30101810745374525104',
            'comment' => 'Импортировано через интеграцию с банком',
            'provider' => 'tochka',
            'provider_account_id' => $record['accountId'],
            'import_is_active' => false,
            'provider_updated_at' => null,
            'provider_account_created_at' => new Carbon($record['registrationDate']),
            'o_auth_account_id' => $oAuthAccount->id,
        ];
    }
}
