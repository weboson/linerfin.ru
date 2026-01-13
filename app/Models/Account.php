<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['subdomain', 'name', 'inn', 'kpp', 'ogrn', 'address', 'legal_address',
        'director_position', 'director_name', 'accountant_position', 'accountant_name', 'is_demo'];


    protected static function boot()
    {
        parent::boot();

        static::created(function(Account $account){
            if(!$account->checkingAccounts->count()){
                $account->checkingAccounts()->create([
                    'name' => 'Наличные',
                    'balance' => 0
                ]);
            }
        });
    }


    public function opf(){
        return $this->belongsTo(OPFType::class, 'opf_type_id');
    }

    public function nds_type(){
        return $this->belongsTo(NDSType::class, 'nds_type_id');
    }

    public function taxation_system(){
        return $this->belongsTo(TaxationSystem::class, 'taxation_system_id');
    }

    public function users(){
        return $this->hasMany(OrganizationUser::class, 'account_id');
    }

    public function counterparties(){
        return $this->hasMany(Counterparty::class);
    }

    public function counterpartyCategories(){
        return $this->hasMany(CounterpartyCategory::class);
    }

    public function projects(){
        return $this->hasMany(Project::class);
    }

    public function budgetItems(){
        return $this->hasMany(BudgetItem::class);
    }

    public function checkingAccounts(){
        return $this->hasMany(CheckingAccount::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function logo_attachment(){
        return $this->belongsTo(Attachment::class);
    }

    public function director_signature(){
        return $this->belongsTo(Attachment::class);
    }

    public function accountant_signature(){
        return $this->belongsTo(Attachment::class);
    }

    public function stamp_attachment(){
        return $this->belongsTo(Attachment::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function bills(){
        return $this->hasMany(Bill::class);
    }


    public function attachments(){
        return $this->hasMany(Attachment::class);
    }

    public function documents(){
        return $this->hasMany(Document::class);
    }

    public function oAuthAccounts(){
        return $this->hasMany(OAuthAccount::class);
    }

}
