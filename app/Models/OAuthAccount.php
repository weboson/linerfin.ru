<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OAuthAccount extends Model
{
    use HasFactory;

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function checkingAccounts() : HasMany
    {
        return $this->hasMany(CheckingAccount::class, 'o_auth_account_id');
    }
}
