<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneToken extends Model
{
    use HasFactory;

    protected $table = 'phone_tokens';
    protected $fillable = ['phone', 'code_hash'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
