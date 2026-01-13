<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItemType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc', 'type', 'category'];

    protected $table = 'budget_item_types';

    public $timestamps = false;

    public function budget_items(){
        return $this->hasMany(BudgetItem::class, 'type_id');
    }
}
