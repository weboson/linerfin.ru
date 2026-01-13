<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'name'];

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function attachment(){
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    public function bill(){
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
