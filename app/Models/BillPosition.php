<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPosition extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'vendor_code', 'unit_price', 'count', 'units', 'account_id'];

    protected static function boot()
    {
        parent::boot();

        // on saving
        static::saving(function (BillPosition $position) {

            // save nds type by bill
            if($position->bill && $position->bill->nds_type)
                $position->nds_type()->associate($position->bill->nds_type);

        });
    }

    public function bill(){
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function nds_type(){
        return $this->belongsTo(NDSType::class);
    }
}
