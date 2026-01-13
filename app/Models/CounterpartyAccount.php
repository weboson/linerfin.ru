<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounterpartyAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'checking_num', 'main_account', 'counterparty_id','account_id',
        'bank_name', 'bank_bik', 'bank_swift', 'bank_inn', 'bank_kpp', 'bank_correspondent'
    ];

    public function counterparty(){
        return $this->belongsTo(Counterparty::class, 'counterparty_id');
    }

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function bank(){
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
