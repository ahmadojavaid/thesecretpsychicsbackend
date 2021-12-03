<?php

namespace App;

use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens, Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id','name', 'email','account_status','token','role','gender','birthday','specific_information','phoneNumber','userFcmToken','devicePlatform','new_status','password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];
    public function chats()
    {
        return $this->hasMany(Chats::class, 'sentBy');
    }

}
