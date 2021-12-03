<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
class Admin extends Authenticatable implements MustVerifyEmail
{
	use Notifiable;
    protected $table = 'admin';
}
