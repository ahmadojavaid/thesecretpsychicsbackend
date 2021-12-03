<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// class PromoCodes extends Model implements AuthenticatableContract 
class PromoCodes extends Model 
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by','promo_code','discount','start_date','expiry_date','assignedToPackage','allowed_multiple_times',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
 
}
