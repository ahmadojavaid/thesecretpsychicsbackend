<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// use Illuminate\Support\Facades\Hash;
// class AdvisorCategories extends Model implements AuthenticatableContract, AuthorizableContract
class AdvisorCategories extends Model
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'advisorId','categoryId',
    ]; 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */ 
}
