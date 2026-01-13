<?php

namespace App\Models;

use App\Models\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'patronymic',
        'phone',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'updated_at',
        'created_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function accounts(){
        return $this->hasMany(Account::class);
    }

    public function userAccounts(){
        return $this->belongsToMany(Account::class, 'account_users');
    }

    public function amoCrmAccounts(){
        return $this->hasMany(AmoCRMAccount::class, 'reference_user_id');
    }

    public function attachments(){
        return $this->hasMany(Attachment::class, 'attachments');
    }

    public function getPhoneForPasswordReset(){
        return $this->phone;
    }

    public function phoneTokens(){
        return $this->hasMany(PhoneToken::class, 'user_id');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
