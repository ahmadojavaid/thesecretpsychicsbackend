<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// class CustomerSupport extends Model implements AuthenticatableContract 
class CustomerSupport extends Model 
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'heading','suggestion','created_at','updated_at','advisorId',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


    
 
}
