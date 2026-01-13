<?php

namespace App\Models;

use App\Scopes\ArchiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;
    protected $table = 'budget_items';

    protected $fillable = ['name', 'comment', 'category', 'archived', 'type_id', 'group_id'];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ArchiveScope);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function nested(){
        return $this->hasMany(BudgetItem::class, 'parent_id');
    }

    public function allNested(){
        return $this->nested()->with('nested');
    }

    public function group(){
        return $this->belongsTo(BudgetItemGroup::class, 'group_id');
    }

    public function type(){
        return $this->belongsTo(BudgetItemType::class, 'type_id');
    }
}
