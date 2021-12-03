<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 24-Jul-19
 * Time: 4:25 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = "conversations";
    protected $fillable = [
        'id','user_id', 'advisor_id'
    ];
}