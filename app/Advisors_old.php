<?php

namespace App;

// use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class Advisors_old extends Authenticatable
{
    use HasMultiAuthApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "advisors";
    protected $fillable = [
        'fullName', 'nickName', 'password', 'email', 'serviceName', 'aboutYourServices', 'videoMessage', 'profileImage', 'contactNumber', 'liveChat', 'videoChat', 'service1', 'service2', 'service3', 'service4', 'service5', 'service6', 'service7', 'service8', 'service9', 'service10', 'service11', 'instructionForOrder', 'profileVideo1', 'profileVideo2', 'profileVideo3', 'profileVideo4', 'profileVideo5', 'imageSection1', 'imageSection2', 'imageSection3', 'imageSection4', 'imageSection5', 'videoSection1', 'videoSection2', 'videoSection3', 'videoSection4', 'videoSection5', 'legalNameOfIndividual', 'dateOfBirth', 'country', 'bankDetails', 'liveCallRate', 'permanentAddress', 'zipCode', 'city', 'termsOfService', 'timeRate', 'paymentThreshold', 'TextChatRate', 'profileVideo', 'thumbnail', 'profileStatus', 'screenName', 'aboutMe', 'advisorFcmToken', 'isNotify', 'threeMinuteVideo', 'devicePlatform', 'expirience', 'username', 'new_status', 'advisor_credit','is_featured',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function ratings()
    {
        return $this->hasMany(AdvisorsReviews::class, 'advisorId');
    }

    public function getipc()
    {
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->where('account_status', 1)
            ->where('profileStatus', 1)
            ->paginate(20);
        //->get();
        return $result;
    }

    public function pendingAdv()
    {
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
            ->where('account_status', 0)
            ->where('profileStatus', 1)
            ->orderby('advisors.created_at','desc')
            ->paginate(10);
        return $result;
    }

    public function approvedAdv()
    {
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
            ->where('account_status', 1)
            ->where('profileStatus', 1)
            ->orderby('advisors.created_at','desc')
            ->paginate(10);
        return $result;
    }

    public function deactivatedAdv()
    {
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
            ->where('account_status', 2)
            ->where('profileStatus', 1)
            ->orderby('advisors.created_at','desc')
            ->paginate(10);
        return $result;
    }

    public function getData($advisor_categories)
    {
        $result = self::with(['Advisorscategories', 'AdvisorsReviews'])
            ->whereIn('id', $advisor_categories)
            ->get();
        return $result;
    }

    public function getAdvisorInfo($id)
    {
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->where('id', $id)
            ->get();
        return $result;
    }

    public function AdvisorsReviews()
    {
        return $this->hasMany('App\AdvisorsReviews', 'advisorId')->join('users', 'users.id', '=', 'advisors_reviews.userId')->select('users.name', 'users.profileImage', 'users.email', 'advisors_reviews.*');//->join('users','users.id','=','advisors_reviews.userId');//->select(DB::raw('(start_time-end_time) AS total_sales'));
    }

    public function Advisorscategories()
    {
        return $this->hasMany('App\AdvisorCategories', 'advisorId')
            ->join('categories', 'categories.id', '=', 'advisor_categories.categoryId');//->select(DB::raw('(start_time-end_time) AS total_sales'));
    }


    public function featured()
    {
        $result = self::with(['AdvisorsReviewsRating', 'Advisorscategories'])
            ->where('advisors.is_featured', '=', 1)
            ->where('account_status','!=',5)
            ->where('account_status','!=',2)
            ->orderByRaw("RAND()")
            ->limit(10)
            //   ->paginate(5);
            ->get();
        return $result;
    }

    public function AdvisorsReviewsRating()
    {
        return $this->hasMany('App\AdvisorsReviews', 'advisorId')->where('rating', '>', '4')->distinct('advisors_reviews.advisorId');//->select(DB::raw('(start_time-end_time) AS total_sales'));
    }

    public function maxRating($maxRatedAdvisors)
    {
        $maxRatedAdvisors; // array of ids
        $placeholders = implode(',', array_fill(0, count($maxRatedAdvisors), '?')); // string for the query
        $result = self::with(['AdvisorsReviews', 'Advisorscategories'])
            ->whereIn('id', $maxRatedAdvisors)
            ->orderByRaw("field(id,{$placeholders})", $maxRatedAdvisors)
            ->paginate(20);
        // ->get();

        return $result;


        // $result = self::with(['AdvisorsReviews','Advisorscategories'])
        //           ->whereIn('id',$maxRatedAdvisors)
        //           ->paginate(20);
        //         //   ->get();
        // return $result;
    }
}