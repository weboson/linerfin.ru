<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'name', 'type', 'size', 'public', 'account_public', 'account_id', 'extension'];
    protected $casts = [
        'meta' => 'json'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function documents(){
        return $this->hasMany(Document::class);
    }

    protected static function boot(){
        parent::boot();
        self::creating(function($model){
            $model->uuid = Str::uuid()->toString();
        });

        self::created(function(Attachment $model){


            // create document
            if(in_array($model->extension, ['doc','docx','xml','xls','xlsx','ppt','pptx'])){
                $doc = new Document([
                    'name' => $model->name,
                    'type' => 'attachment'
                ]);

                $doc->attachment()->associate($model);
                if($model->account)
                    $doc->account()->associate($model->account);
                if($model->user)
                    $doc->user()->associate($model->user);

                $doc->save();
            }
        });
    }
}
