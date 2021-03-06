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

    public function store(){
        return $this->belongsTo('App\Models\User','user_id','id')->with('profile');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->with('profile');
    }

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor','vendor_id','id');
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id');
    }
}
