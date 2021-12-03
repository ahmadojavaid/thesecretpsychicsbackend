<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// use Illuminate\Support\Facades\Hash;
// class Orders extends Model implements AuthenticatableContract, AuthorizableContract
class Orders extends Model
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'advisorId', 'userId','order_heading','order_details','order_video', 'order_status','categoryId','reply_heading','reply_details','reply_Video','readStatus','isSeen','isCompleted','isReviewed','user_order_seen_status',
    ]; 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */ 
}

 