<?php

namespace App\Http\Services\Bank;

use Carbon\Carbon;

class TochkaToCounterPartyMap
{
    public static function map($account, $counterParty) {
        return [
            'account_id' => $account->id,
            'name' => $counterParty['name'] ?? null,
            'inn' => $counterParty['inn'] ?? null,
            'type' => strlen($counterParty['inn']) == 10 ? 'LEGAL' : 'INDIVIDUAL',
            'kpp' => $counterParty['kpp'] ?? null,
        ];
    }
}
