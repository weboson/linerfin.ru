<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxationSystem extends Model
{
    use HasFactory;

    protected $table = 'taxation_systems';
    public $timestamps = false;
}
