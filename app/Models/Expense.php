<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Expense extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function expense_type(){
        return $this->belongsTo('App\Models\Expense_type','expense_type_id','id');
    }
}