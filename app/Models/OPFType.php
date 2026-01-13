<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OPFType extends Model
{
    use HasFactory;

    protected $table = 'opf_types';
    public $timestamps = false;

    protected $fillable = ['name', 'code', 'short', 'type', 'for_individual', 'for_legal'];
}
