<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 29-Jul-19
 * Time: 9:57 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = "admin_notifications";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id','support_id','advisor_id','client_id','notification_title','notification_msg','notification_type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}