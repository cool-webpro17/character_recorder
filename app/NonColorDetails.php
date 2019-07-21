<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NonColorDetails extends Model
{
    //
    protected $fillable = ['value_id',
        'negation',
        'pre_constraint',
        'main_value',
        'post_constraint'];
}
