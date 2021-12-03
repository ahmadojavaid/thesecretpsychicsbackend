<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 19-Jul-19
 * Time: 10:23 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $table = "experience";
    protected $fillable = [
        'id','text'
    ];
}