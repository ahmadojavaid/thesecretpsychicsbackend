<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// class BecomingAnAdvisors extends Model implements AuthenticatableContract 
class BecomingAnAdvisors extends Model 
{
    use Authenticatable, Authorizable; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','text'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
 
}
