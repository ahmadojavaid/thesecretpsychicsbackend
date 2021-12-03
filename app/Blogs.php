<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
// use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; 
// class Blogs extends Model implements AuthenticatableContract 
class Blogs extends Model
{
    // use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'blog_author','blog_image','author_image','created_at','updated_at','blog_title','blog_desc','views','blog_type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */ 
}
