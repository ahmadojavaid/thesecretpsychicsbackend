<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// use Illuminate\Support\Facades\Hash;
// class Payments extends Model implements AuthenticatableContract, AuthorizableContract
class Payments extends Model
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId','advisorId','credit','refrence','system_fee','order_id','message_id','balance'

    ]; 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */ 

    
}
