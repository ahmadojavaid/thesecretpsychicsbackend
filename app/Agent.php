<?php

namespace App;

use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Models\API\AgentPersonalDetail;
use App\Http\Models\API\AgentPartnerDetail;
use App\Http\Models\API\AgentOffender;
use App\Http\Models\API\AgentEmploymentDetail;
use App\Http\Models\API\AgentArmedPoliceDetail;
use App\Http\Models\API\AgentSelfEmployment;
use App\Http\Models\API\AgentUnEmploymentDetail;
use App\Http\Models\API\AgentPersonalRefrence;
use App\Http\Models\API\AgentBadges;
use App\Http\Models\API\Badge;

class Agent extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasMultiAuthApiTokens, Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "advisors";
    protected $fillable = [
        'first_name','last_name', 'email', 'password','phone','ss_number','sia_number','description','attire_type',
        'have_driving_license','license_image','profile_image','have_vehicle','account_status','email_verified_at',
        'fcm_token','device_type','is_available','current_lat','current_long','timezone','verification_code'
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
  
}
