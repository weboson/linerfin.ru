<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounterpartyCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'comment'];

    public function counterparties(){
        return $this->hasMany(Counterparty::class);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }
}
