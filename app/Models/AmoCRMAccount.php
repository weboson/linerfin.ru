<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

class AmoCRMAccount extends Model
{
    use HasFactory;

    protected $table = 'amocrm_accounts';

    protected $fillable = ['access_token', 'refresh_token', 'expires', 'base_domain',
        'subdomain', 'hash', 'client_id', 'subdomain', 'email', 'amo_user_id',
        'timezone', 'tariff_name', 'users_count', 'paid_till_date', 'paid_from',
        'bill_closing', 'task_creating'];

    protected $casts = [
        'paid_till_date' => 'datetime',
        'paid_from' => 'datetime',
    ];


    public function personal_access_token(){
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }

    public function bills(){
        return $this->hasMany(Bill::class, 'amocrm_account_id');
    }

    public function referenceUser(){
        return $this->belongsTo(User::class, 'reference_user_id');
    }
}
