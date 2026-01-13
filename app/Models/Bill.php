<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Bill extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        // def
        'num', 'sum', 'status', 'enable_attachments',

        // dates
        'pay_before', 'rejected_at', 'paid_at', 'issued_at', 'realized_at',

        // payer
        'payer_phone', 'payer_email',

        // comments
        'comment', 'reject_comment',

        // counterparty
        'counterparty_name',
        'counterparty_inn',
        'counterparty_kpp',
        'counterparty_address',

        // other
        'link', 'private_key', 'access', 'amocrm_lead_id', 'amocrm_customer_id'
    ];

    protected $dates = ['pay_before', 'issued_at', 'paid_at', 'rejected_at', 'realized_at'];



    // Bill account
    public function account(){
        return $this->belongsTo(Account::class, 'account_id')->withTrashed();
    }

    public function nds_type(){
        return $this->belongsTo(NDSType::class, 'nds_type_id');
    }

    // Counterparty
    public function counterparty(){
        return $this->belongsTo(Counterparty::class, 'counterparty_id')->withTrashed();
    }

    // Positions
    public function positions(){
        return $this->hasMany(BillPosition::class, 'bill_id');
    }

    // Checking account
    public function checking_account(){
        return $this->belongsTo(CheckingAccount::class);
    }

    // Signatures
    public function signature_list(){
        return $this->hasMany(BillSignature::class);
    }

    // Signatures with attachments
    public function signature_list_with_attachments(){
        return $this->hasMany(BillSignature::class)->with([
            'signature_attachment'
        ]);
    }


    // amoCRM model
    public function amocrm_account(){
        return $this->belongsTo(AmoCRMAccount::class, 'amocrm_account_id');
    }


    // Logo
    public function logo_attachment(){
        return $this->belongsTo(Attachment::class, 'logo_attachment_id');
    }

    // Stamp
    public function stamp_attachment(){
        return $this->belongsTo(Attachment::class, 'stamp_attachment_id');
    }

    // Related transactions
    public function transactions(){
        return $this->hasMany(Transaction::class, 'bill_id');
    }

    public function documents(){
        return $this->hasMany(Document::class, 'bill_id');
    }
}
