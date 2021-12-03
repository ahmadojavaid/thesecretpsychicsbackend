<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 16-Jul-19
 * Time: 12:52 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = "faq";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question','answer',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}