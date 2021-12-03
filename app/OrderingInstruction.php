<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 19-Jul-19
 * Time: 9:35 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class OrderingInstruction extends Model
{
    protected $table = "ordering_instructions";
    protected $fillable = [
        'id','text'
    ];
}