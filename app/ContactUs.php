<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 25-Jul-19
 * Time: 4:11 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = "contact_us";
    protected $fillable = [
        'id','first_name', 'last_name', 'email', 'message'
    ];
}