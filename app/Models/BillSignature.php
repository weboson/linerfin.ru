<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSignature extends Model
{
    use HasFactory;

    protected $table = 'bill_signature_list';
    public $timestamps = false;

    protected $fillable = ['full_name', 'position'];

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function bill(){
        return $this->belongsTo(Bill::class);
    }

    public function signature_attachment(){
        return $this->belongsTo(Attachment::class, 'signature_attachment_id');
    }
}
