<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItemGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc', 'type'];

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function budgetItems(){
        return $this->hasMany(BudgetItem::class, 'type_id');
    }
}
