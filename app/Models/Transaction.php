<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['type', 'amount', 'amount_without_vat', 'description', 'date', 'total_balance',
        'from_ca_balance', 'to_ca_balance', 'made_at', 'external_id', 'is_active',  'account_id', 'from_ca_id', 'to_ca_id', 'counterparty_id'];
    protected $dates = ['date', 'made_at'];


    // Model Callbacks
    protected static function boot()
    {
        parent::boot();

        // on saving
        // -> set VAT type id (nds_type_id)
        // -> update amount without VAT
        static::saving(function(Transaction $transaction) {

            // def nds_type_id by account
            if(!$transaction->nds_type_id && $transaction->type === 'expense'){
                $account = $transaction->account;
                if($account && $nds_type = $account->nds_type)
                    $transaction->nds_type()->associate($nds_type);
            }


            // update amount without VAT
            $amount = $transaction->amount;
            if($transaction->nds_type && $transaction->nds_type->percentage)
                $amount -= $amount * $transaction->nds_type->percentage / 100;
            $transaction->amount_without_vat = $amount;

        });
    }


    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function nds_type(){
        return $this->belongsTo(NDSType::class, 'nds_type_id');
    }

    public function fromCheckingAccount(){
        return $this->belongsTo(CheckingAccount::class, 'from_ca_id');
    }

    public function toCheckingAccount(){
        return $this->belongsTo(CheckingAccount::class, 'to_ca_id');
    }

    public function counterparty(){
        return $this->belongsTo(Counterparty::class, 'counterparty_id');
    }

    public function budgetItem(){
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function bill(){
        return $this->belongsTo(Bill::class, 'bill_id');
    }

}
