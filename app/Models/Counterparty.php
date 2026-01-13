<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counterparty extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'inn', 'ogrn', 'kpp', 'address', 'legal_address', 'type',
        'amo_company_id', 'account_id'
    ];

    public function category(){
        return $this->belongsTo(CounterpartyCategory::class, 'category_id');
    }

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function opf(){
        return $this->belongsTo(OPFType::class, 'opf_type_id');
    }

    public function contacts(){
        return $this->hasMany(Contact::class, 'counterparty_id')->orderByDesc('main_contact');
    }

    public function accounts(){
        return $this->hasMany(CounterpartyAccount::class, 'counterparty_id')->orderByDesc('main_account');
    }

    public function bill_templates(){
        return $this->bills()->where('status', 'template');
    }

    public function bills(){
        return $this->hasMany(Bill::class, 'counterparty_id');
    }

}
