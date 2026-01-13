<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NDSType extends Model
{
    use HasFactory;

    protected $table = 'nds_types';
    protected $fillable = ['name', 'percentage'];

    public $timestamps = false;
}
