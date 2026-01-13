<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckingAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'provider_account_created_at' => 'date',
        'provider_account_updated_at' => 'datetime',
        'import_is_active' => 'boolean',
    ];

    protected $fillable = ['name', 'num', 'balance',
        'bank_name', 'bank_bik', 'bank_correspondent', 'bank_inn', 'bank_kpp', 'bank_swift', 'comment', 'o_auth_account_id',
        'provider', 'provider_account_id', 'import_is_active', 'provider_account_updated_at', 'provider_account_created_at'];

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions(){
        return Transaction::where('from_ca_id', $this->id)
            ->orWhere('to_ca_id', $this->id)->get();
    }

    public function correct_history(){
        return $this->hasMany(BalanceCorrectHistory::class);
    }
}
