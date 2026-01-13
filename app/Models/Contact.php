<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'surname', 'patronymic', 'email', 'phone', 'main_contact'];

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function counterparty(){
        return $this->belongsTo(Counterparty::class, 'counterparty_id');
    }

}
