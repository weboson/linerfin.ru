<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceCorrectHistory extends Model
{
    use HasFactory;

    protected $table = 'balance_correct_history';

    protected $fillable = ['old_balance', 'new_balance'];

    public function checkingAccount(){
        return $this->belongsTo(CheckingAccount::class);
    }
}
