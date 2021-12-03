<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 05-Jul-19
 * Time: 3:52 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "password_reset";

    protected $fillable = [
        'user_id', 'password_code_hash', 'user_type','user_type'
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];
}