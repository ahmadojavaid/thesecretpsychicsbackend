<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\AdvisorCategories;
use App\Blogs;
use App\Mail\AccountApproved;
use App\Mail\AccountRejected;
use App\Mail\PasswordReset;
use App\Mail\PasswordResetNew;
use function FastRoute\TestFixtures\empty_options_cached;
use Illuminate\Http\Request;
use App\PasswordReset as ResetPasswordModel;
use Illuminate\Support\Facades\Auth;
use Modules\Advisor\Http\Controller\Api\ApiController;
use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\resources\emails\mailExample;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Testing\Fakes\MailFake;
use App\config\services;
use App\Advisor as Advisors;
// use App\Advisor as Advisors;
use App\ClientDetails;
use App\FavouriteAdvisors;
use App\User;
use GuzzleHttp\Client;
use Log;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Client as GuzzleHttpClient;
use DB;
use Carbon\Carbon;
use Excel;
// use Maatwebsite\Excel\Facades\Excel;


use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class AdvisorController extends ApiController
{
  
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {

            $array = [];

            $admin = new Advisors();

            $admin->email = $request->input('email');

            if (empty($admin->email)) {
                array_push($array, "email Required");
            }

            if (Advisors::where('email', '=', Input::get('email'))->exists()) {
                return response()->json(['statusCode' => '400', 'statusMessage' => 'email Already Exists', 'Result' => NULL]);
            }
            if (Advisors::where('username', '=', $request->input('username'))->exists()) {

                return response()->json(['statusCode' => '400', 'statusMessage' => 'username Already Exists', 'Result' => NULL]);
            } else {
                // if ($request->has('profileImage') && $request->has('profileVideo')) {

                // if (!$admin->password)
                // {
                //   array_push($array, "Password Required");
                // }
                if (count($array) > 0) {
                    return response()->json(['statusCode' => '400', 'statusMessage' => 'Fill the given fields', 'Result' => $array]);
                }
                //.....uploadingVideo

                $admin = Advisors::create($request->only(['email', 'password', 'username', 'new_status','language']));

                // $extension = $request->file('profileVideo')->getClientOriginalExtension();

                // $photo = time().'-'.$request->file('profileVideo')->getClientOriginalName();

                // $destination =  'api/public/uploads/';

                // $path = $request->file('profileVideo')->move($destination, $photo);

                // $admin->profileVideo = $path;

                //        //uploading Image

                // $photo = time().'-'.$request->file('profileImage')->getClientOriginalName();

                // $destination =  'api/public/uploads/images/profileImages/';

                // $path = $request->file('profileImage')->move($destination, $photo);

                //      // $entityBody =$request['profileImage'];
                //      // $format = '.png';
                //      // $imageName = $admin->id.$format;

                //      // $directory = "/images/profileImages/";
                //      // $path = base_path()."/public".$directory;
                //      // $data = base64_decode($entityBody);

                //      // file_put_contents($path.$imageName, $data);


                //      $admin->profileImage = $path;

                // $admin->save();

                // $token = bin2hex(openssl_random_pseudo_bytes(25));


                DB::table('advisors')->where('id', $admin->id)->update(array('isOnline' => 1,
                    'liveChat' => 1, 'videoChat' => 1));
                // $admin->token = $token;

                //  $aaa =  json_decode($request->categories);

                //   if ($request->has('categories')){

                //   $aaa =  json_decode($request->categories);

                //     for($i=0; $i <count($aaa) ; $i++) {

                //         $temp = array("advisorId" => $admin->id, "categoryId" => $aaa[$i]);

                //        $lists =  DB::table('advisor_categories')->insertGetId($temp);
                //     }
                // }

                //Mail::send('emails.verify',["data" => $admin], function($message) use ($admin) {
                // $message->from('baqalah1@gmail.com', 'FUSE');
                // $message->to($admin->email, 'Mail Confirmation')->subject('Testing Mail');
                //});

                $accessToken = $admin->createToken('Agent Password Grant Token')->accessToken;
                $collection = collect(['id' => $admin->id, 'email' => $admin->email, 'token' => $accessToken]);
                $collection->toJson();

                DB::commit();

                return response()->json(['statusCode' => '1', 'statusMessage' => 'Account Successfully Created', 'Result' => $collection]);

                // }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        }
    }

    public function confirm(Request $request, $id)
    {
        $admin = Advisors::where('token', '=', $id)->first();
        if (!$admin) {
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Something Went wrong', 'Result' => $admin]);
        } else {
            $account_status = 1;
            DB::table('Advisorss')->where('token', $id)->update(array('account_status' => $account_status));

            $admin->save();

            return redirect('http://dr-romia.com/#/pages/auth/login-2');
        }
    }
    public function doLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required',
            'advisorFcmToken' => 'required',
            'devicePlatform' => 'required', 
        ],
            [
                "advisorFcmToken.required" => 'FCM Token is required',
                "devicePlatform.required" => 'Device type value is required'
            ]);
        if ($validator->fails()) {
            $errorMsg = $validator->messages();
            $this->apiHelper->statusCode     = 0;
            $this->apiHelper->statusMessage  = 'Validation Error!';
            $arr  = [
                'response' => $errorMsg, 
            ];
            $this->apiHelper->result         = $arr;
            return response()->json($this->apiHelper->responseParse(),422);
        }
        $email              = $request->email;
        $password           = $request->password;
        $fcmToken           = $request->advisorFcmToken;
        $deviceType         = $request->devicePlatform; 
        $oldPassword = Advisors::where('email', '=', $email)
                                ->select('password', 'account_status')
                                ->first();
        if (!empty($oldPassword)) {
            if ($oldPassword->account_status == 1) {
                if (Hash::check($password, $oldPassword->password)) {
                    $advisor = Advisors::where('email', '=', $email)->first();
                    Advisors::where('email', '=', $email)
                        ->update([
                            "advisorFcmToken" => $fcmToken,
                            "devicePlatform" => $deviceType, 
                        ]);
                    $accessToken = $advisor->createToken('Agent Password Grant Token')->accessToken;
                    $response  = [
                                'token' => $accessToken,
                                'user'  => $advisor
                            ];
                    $this->apiHelper->statusCode     = 1;
                    $this->apiHelper->statusMessage  = 'Logged In';
                    $this->apiHelper->result         = $response;
                    return response()->json($this->apiHelper->responseParse(),200);
                } else {
                   $this->apiHelper->statusCode     = 0;
                   $this->apiHelper->statusMessage  = 'Your Credential is wrong.';
                   return response()->json($this->apiHelper->responseParse(),403);
                }
            } else {
                $this->apiHelper->statusCode     = 0;
                $this->apiHelper->statusMessage  = 'You Profile is under review. Please check your email in 24 hours';
                return response()->json($this->apiHelper->responseParse(),403);
            }
        } else {
           $this->apiHelper->statusCode     = 0;
           $this->apiHelper->statusMessage  = 'Your Credential is wrong.';
           return response()->json($this->apiHelper->responseParse(),403);
        }
    }

    public function uploadVid(Request $request)
    {
        $advisorId = $request->input("advisorId");

        $validation = Advisors::where('id','=',$advisorId)->select('profileVideo')->first();

        if(!empty($validation)){
            $path = base_path() . "/public" . $validation->profileVideo;
            File::delete($path);
        }
        $extension = $request->file('profileVideo')->getClientOriginalExtension();

        $photo = time() . '-' . $request->file('profileVideo')->getClientOriginalName();

        $temp = explode('.', $photo);
        $ext = array_pop($temp);
        $name = implode('.mp4', $temp);

        $ext = $name.'.mp4';

        $destination = 'api/public/uploads/';

        $path = $request->file('profileVideo')->move($destination, $ext);

        DB::table('advisors')->where('id', $advisorId)->update(array('profileVideo' => $path));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Profile Video Uploaded', 'result' => NULL]);

        // $admin->profileVideo = $path;
    }

    public function assignCat(Request $request)
    {
        $string = trim($request->categories, ".");
        $split = explode(",", $string);

        // return count($split);

        // $string = $request->categories;
        // $string = preg_replace('/\.$/', '', $string); //Remove dot at end if exists
        // $array = explode(', ', $string); //split string into array seperated by ', '

        // $array = str_split($request->categories);
        // return $array;
        // $aaa =  json_decode($request->categories);

        if ($request->has('categories')) {

            // $aaa =  json_decode($split);
            DB::table('advisor_categories')->where('advisorId', $request->advisorId)->delete();

            for ($i = 0; $i < count($split); $i++) {

                $temp = array("advisorId" => $request->advisorId, "categoryId" => $split[$i]);

                $lists = DB::table('advisor_categories')->insertGetId($temp);
            }
        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'categories Assigned', 'Result' => $split]);

    }

    public function addTovisitor(Request $request)
    {
        $advisorId = Input::get("advisorId");
        DB::table('visitors')->insert(
            array(
                'advisorId' => $advisorId
            )
        );
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Visitor Added', 'Result' => $advisorId]);

    }

    public function addTofav(Request $request)
    {
        $advisorId = Input::get("advisorId");
        DB::table('visitors')->insert(
            array(
                'advisorId' => $advisorId
            )
        );

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Visitor Added', 'Result' => $advisorId]);

    }

    public function updateAdvisorInfo($id, Request $request)
    {
        $Category = Advisors::find($id);
        $isOnline = $request->get('isOnline');
        $liveChat = $request->get('liveChat');
        $videoChat = $request->get('videoChat');
        $categories = $request->get('categories');

        if(empty($liveChat) || empty($videoChat) || empty($isOnline) ){
            $liveChat = 1;
            $videoChat = 1;
            $isOnline = 1;
        }

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }

        if ($request->has('profileImage')) {

            if(!empty($categories)){
                AdvisorCategories::where('advisorId','=',$id)->delete();
                foreach ($categories as $cat){
                    AdvisorCategories::insert([
                        "advisorId" => $id, "categoryId" => $cat
                    ]);
                }
            }

            $Category->update($request->except(['profileImage']));
            $previousImage = $Category->profileImage;
            if(!empty($previousImage)){
                $path = base_path() . "/public" . $previousImage;
                File::delete($path);
            }
            $unique = bin2hex(openssl_random_pseudo_bytes(10));

            $format = '.png';

            $entityBody = $request['profileImage'];// file_get_contents('php://input');

            $imageName = $Category->id . $unique . $format;

            $directory = "/images/profileImages/";

            $path = base_path() . "/public" . $directory;

            $data = base64_decode($entityBody);

            file_put_contents($path . $imageName, $data);

            $response = $directory . $imageName;

            $Category->profileImage = $response;

            // DB::table('advisors')->where('id', $id)->update(array('profileStatus' =>$request->profileStatus));
            DB::table('advisors')->where('id', $id)->update(array('profileImage' => $response));
            DB::table('advisors')->where('id', $id)->update(array('new_status' => 1, 'isOnline' => $isOnline,
                'liveChat' => $liveChat,'videoChat' => $videoChat));

            // $aaa =  json_decode($request->categories);

            //   if ($request->has('categories')){

            //     // $aaa =  json_decode($request->categories);

            //     for($i=0; $i <count($request->has('categories')) ; $i++) {

            //         $temp = array("advisorId" => $id, "categoryId" => $request->has('categories')[$i]);

            //        $lists =  DB::table('advisor_categories')->insertGetId($temp);
            //     }
            // }
        } else {
            if(!empty($categories)){
                AdvisorCategories::where('advisorId','=',$id)->delete();
                foreach ($categories as $cat){
                    AdvisorCategories::insert([
                        "advisorId" => $id, "categoryId" => $cat
                    ]);
                }
            }
            // DB::table('advisors')->where('id', $id)->update(array('profileStatus' => 1));
            $Category->update($request->except(['author_image']));
            DB::table('advisors')->where('id', $id)->update(array('new_status' => 1));
        }

        $Category->update($request->except(['email', 'token', 'profileImage']));
        DB::table('advisors')->where('id', $id)->update(array('new_status' => 1, 'isOnline' => $isOnline,
            'liveChat' => $liveChat,'videoChat' => $videoChat));

        $Category = DB::table('advisors')->where('id', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors Data is Updated', 'Result' => $Category]);
    }

    public function showAdvisorInfo($id, Request $request)
    {

        $userId = $request->input('userId');

        $Advisors = new Advisors();

        $temp = $Advisors->getAdvisorInfo($id);

        for ($i = 0; $i < count($temp); $i++) {

            $checkfavAdvisor = DB::table('favourite_advisors')
                ->where('userId', '=', $userId)
                ->where('advisorId', '=', $temp[$i]->id)
                ->first();

            $ratingToBeexcl = DB::table('advisors_reviews')
                ->where('advisorId', '=', $temp[$i]->id)
                ->avg('rating');


            $revenue = DB::table('payments')
                ->where('advisorId', '=', $id)
                ->sum('credit');


            $temp[$i]->{'revenue'} = $revenue;

            if ($ratingToBeexcl) {
                $temp[$i]->{'rating'} = $ratingToBeexcl;
            } else {
                $temp[$i]->{'rating'} = 0;
            }

            if ($checkfavAdvisor) {
                $temp[$i]->{'favuorited'} = 1;
            } else
                $temp[$i]->{'favuorited'} = 0;
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing Advisor Information', 'Result' => $temp]);
    }

    //     public function showAdvisorInfo($id,Request $request)
    //  {      
    //         // $Category=Advisors::find($id); 

    //          $userId = $request->input('userId'); 

    //          $Advisors = new Advisors();

    //          $temp = $Advisors->getAdvisorInfo($id);

    //           for ($i=0; $i <count($temp) ; $i++) { 

    //              $checkfavAdvisor = DB::table('favourite_advisors')  
    //              ->where('userId','=',$userId)
    //              ->where('advisorId','=',$temp[$i]->id)
    //              ->first();

    //              if($checkfavAdvisor){ 
    //               $temp[$i]->{'favuorited'} =1;
    //             }else
    //               $temp[$i]->{'favuorited'} =0;
    //           }  


    //   return response()->json(['statusCode'=>'1','statusMessage'=>'showing Advisor Information','Result'=>$temp]);
    // } 
    public function showPendingAdvisors(Request $request)
    {
        $Advisors = new Advisors();

        $temp = $Advisors->pendingAdv();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing pending Advisors', 'Result' => $temp]);
    }

    public function showApprovedAdvisors(Request $request)
    {
        $Advisors = new Advisors();

        $temp = $Advisors->approvedAdv();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing Approved Advisors', 'Result' => $temp]);
    }

    public function showDeactivatedAdvisors()
    {
        $Advisors = new Advisors();

        $temp = $Advisors->deactivatedAdv();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing Deactivated Advisors', 'Result' => $temp]);
    }

    public function showAdvisor($id, Request $request)
    {
        // $Category=Advisors::find($id);
        $Advisors = new Advisors();
        $temp = Advisors::find($id);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing Advisor Information', 'Result' => $temp]);
    }

    //......... ....Add To Favourite Advisors

    public function storefavAdvisor(Request $request)
    {
        try {

            $FavouriteAdvisors = FavouriteAdvisors::create($request->all());

            return response()->json(['statusCode' => '1', 'statusMessage' => 'Favourite Advisors Created', 'Result' => $FavouriteAdvisors]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        }
    }

    public function generateProfileLink(Request $request)
    {
        try {

            // return URL::to('/profileLink?userName='.$request->input('userName'));

            $userName = $request->input('userName');

            $getAdvisorId = DB::table('advisors')
                ->where('username', '=', $userName)
                ->pluck('id')
                ->first();

            $Advisors = new Advisors();

            $temp = $Advisors->getAdvisorInfo($getAdvisorId);

            return response()->json(['statusCode' => '1', 'statusMessage' => 'Favourite Advisors Created', 'Result' => $temp]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        }
    }

    //   public function updatefavAdvisor($id,Request $request)
    //  {
    //   $FavouriteAdvisors=FavouriteAdvisors::find($id);
    //     if(!$FavouriteAdvisors)
    //    {
    //     return response()->json(['statusCode'=>'0','statusMessage'=>'Record Not Found','Result'=>NULL]);
    //    }
    //       $FavouriteAdvisors->update($request->all());
    //     return response()->json(['statusCode'=>'1','statusMessage'=>'Favourite Advisors is Updated','Result'=>$FavouriteAdvisors]);
    // }


    public function destroyfavAdvisor(Request $request)
    {

        $userId = $request->input('userId');
        $advisorId = $request->input('advisorId');

        if (!$userId && $advisorId) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $del = DB::table('favourite_advisors')
            ->where('userId', '=', $userId)
            ->where('advisorId', '=', $advisorId)
            ->delete();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'FavouriteAdvisors deleted', 'Result' => $del]);
    }

    public function showfavAdvisor(Request $request)
    {

        //$FavouriteAdvisors=FavouriteAdvisors::with('users')->get();

        $userId = $request->input('userId');

        $favourite_advisors = DB::table('favourite_advisors')
            ->where('userId', '=', $userId)
            ->pluck('advisorId');
        // return $favourite_advisors;
        $Advisors = new Advisors();

        $temp = $Advisors->getData($favourite_advisors);
        // return $temp;
        for ($i = 0; $i < count($temp); $i++) {

            $rating = DB::table('advisors_reviews')
                ->where('advisorId', '=', $temp[$i]->id)
                ->avg('rating');

            if ($rating) {
                // return $rating[1];
                $temp[$i]->{'rating'} = $rating;
            } else
                $temp[$i]->{'rating'} = 0;
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing favourite advisors', 'Result' => $temp]);


        // return response()->json(['statusCode'=>'1','statusMessage'=>'showing all Departments','Result'=>$FavouriteAdvisors]);
    }

    public function updateOnlineStatus($id, Request $request)
    {
        $Category = Advisors::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $status = $request->input('status');
        DB::table('advisors')->where('id', $id)->update(array('isOnline' => $status, 'liveChat' => $status, 'videoChat' => $status));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors status is Updated', 'Result' => NULL]);
    }

    public function approveOrReject($id, Request $request)
    {
        $Category = Advisors::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $account_status = $request->input('account_status');
        DB::table('advisors')->where('id', $id)->update(array('account_status' => $account_status));
        $advisorDetails = DB::table('advisors')->where('id', $id)->select('screenName','email')->first();
        if($account_status == 1){
            Mail::to($advisorDetails->email)->send(new AccountApproved($advisorDetails->screenName));
        }
        else{
            Mail::to($advisorDetails->email)->send(new AccountRejected($advisorDetails->screenName));
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors status is Updated', 'Result' => NULL]);
    }

    public function updatenotifyStatus($id, Request $request)
    {
        $Category = Advisors::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $status = $request->input('status');
        DB::table('advisors')->where('id', $id)->update(array('isNotify' => $status));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors status is Updated', 'Result' => NULL]);
    }

    public function search(Request $request)
    {

        if ($request->has('rating')) {

            $maxRatedAdvisors = \DB::table('advisors_reviews')
                ->join('advisors', 'advisors.id', '=', 'advisors_reviews.advisorId')
                ->select('advisors.id')
                ->selectRaw('AVG(advisors_reviews.rating) AS average_rating')
                ->groupBy('advisors.id')
                ->havingRaw('AVG(advisors_reviews.rating) >= ?', [3])
                ->pluck('advisors.id');

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing rated advisors advisors', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);

        }
        if ($request->has('reviews')) {

            $maxRatedAdvisors = DB::table('advisors')
                ->orderBy('feedbackCount', 'DESC')
                ->pluck('id');
            // return $maxRatedAdvisors;

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing rated advisors advisors', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);

        }


        if ($request->has('lowToHigh')) {


            $maxRatedAdvisors = DB::table('advisors')
                ->orderBy('TextChatRate', 'asc')
                ->pluck('id');
            // return $maxRatedAdvisors;

            // for ($i=0; $i <count($getmaxReviewAdvisors) ; $i++) {


            //      $maxReviewAdvisors = DB::table('advisors_reviews')
            //           ->where('advisorId', '=',$getmaxReviewAdvisors[$i])
            //           // ->orderBy('advisors_reviews.feedback')
            //           ->count('feedback')
            //           ->pluck('advisorId');

            //          // return $maxReviewAdvisors;
            //           }

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing rated advisors advisors', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);
        }

        if ($request->has('highToLow')) {

            $maxRatedAdvisors = DB::table('advisors')
                ->orderBy('TextChatRate', 'desc')
                ->pluck('id');
            // return $maxRatedAdvisors;

            // for ($i=0; $i <count($getmaxReviewAdvisors) ; $i++) {


            //      $maxReviewAdvisors = DB::table('advisors_reviews')
            //           ->where('advisorId', '=',$getmaxReviewAdvisors[$i])
            //           // ->orderBy('advisors_reviews.feedback')
            //           ->count('feedback')
            //           ->pluck('advisorId');

            //          // return $maxReviewAdvisors;
            //           }

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }
            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing rated advisors advisors', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);

        }

        // return $favourite_advisors;
        // $Advisors = new Advisors();

        // $temp = $Advisors->getData($favourite_advisors);
        // // return $temp;
        //  for ($i=0; $i <count($temp) ; $i++) {

        //      $rating = DB::table('advisors_reviews')
        //      ->where('advisorId','=',$temp[$i]->id)
        //      ->avg('rating');

        //      if($rating){
        //       // return $rating[1];
        //       $temp[$i]->{'rating'} =$rating;
        //     }else
        //       $temp[$i]->{'rating'} =0;
        //   }


        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing favourite advisors', 'All psychics' => $temp]);


        // return response()->json(['statusCode'=>'1','statusMessage'=>'showing all Departments','Result'=>$FavouriteAdvisors]);
    }

    public function filter(Request $request)
    {

        if ($request->has('timeRate')) {

            $lowerLimit = $request->input('lowerLimit');
            $upperLimit = $request->input('upperLimit');

            $maxRatedAdvisors = \DB::table('advisors')
                ->whereBetween('timeRate', [$lowerLimit, $upperLimit])
                ->pluck('advisors.id');
            // return $maxRatedAdvisors;

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing timeRate advisors ', 'All psychics' => $temp, 'featured' => $featured]);

        }

        if ($request->has('numberofReview')) {

            $lowerLimit = 0;
            $upperLimit = $request->input('upperLimit');

            $maxRatedAdvisors = \DB::table('advisors')
                ->whereBetween('feedbackCount', [$lowerLimit, $upperLimit])
                ->orderBy('feedbackCount', 'desc')
                ->pluck('advisors.id');

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing numberofReview advisors', 'All psychics' => $temp, 'featured' => $featured]);

        }

        if ($request->has('liveChat')) {

            $maxRatedAdvisors = \DB::table('advisors')
                // ->whereBetween('feedbackCount', [$lowerLimit, $upperLimit])
                ->where('isOnline', 1)
                // ->orderBy('feedbackCount','desc')
                ->pluck('advisors.id');

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {

                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing liveChat advisors ', 'All psychics' => $temp, 'featured' => $featured]);

        }

        if ($request->has('noliveChat')) {
            // return 'sss';
            $maxRatedAdvisors = \DB::table('advisors')
                ->where('isOnline', 0)
                ->pluck('advisors.id');

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing liveChat advisors ', 'All psychics' => $temp, 'featured' => $featured]);

        }

        if ($request->has('both')) {
            // return 'sss';
            $maxRatedAdvisors = \DB::table('advisors')
                // ->whereBetween('feedbackCount', [$lowerLimit, $upperLimit])
                // ->where('isOnline', 1)
                // ->orderBy('feedbackCount','desc')
                ->pluck('advisors.id');

            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($maxRatedAdvisors);
            // return $temp;
            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing rated advisors advisors', 'All psychics' => $temp, 'featured' => $featured]);

        }

    }

    public function advanceSearch(Request $request)
    {

        $lowerLimitRate = $request->input('lowerLimitRate');
        $upperLimitRate = $request->input('upperLimitRate');
        $lowerLimitReview = $request->input('lowerLimitReview');
        $upperLimitReview = $request->input('upperLimitReview');
        $online = $request->input('online');
        $offline = $request->input('offline');
        $both = $request->input('both');

        if ($request->has('lowerLimitRate') &&
            $request->has('upperLimitRate') &&
            $request->has('lowerLimitReview') &&
            $request->has('upperLimitReview') &&
            $request->has('online')) {

            $searchAdvisors = DB::table('advisors')
                ->whereRaw("timeRate BETWEEN $lowerLimitRate AND $upperLimitRate")
                ->whereRaw("feedbackCount BETWEEN $lowerLimitReview AND $upperLimitReview")
                ->where('isOnline', 1)
                ->where('account_status','!=', 5)
                ->pluck('advisors.id');

            //return $searchAdvisors;
            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($searchAdvisors);

            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }


            //To get featured
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);

        }
        if ($request->has('lowerLimitRate') &&
            $request->has('upperLimitRate') &&
            $request->has('lowerLimitReview') &&
            $request->has('upperLimitReview') &&
            $request->has('offline')) {

            $searchAdvisors = DB::table('advisors')
                ->where('isOnline', 0)
                ->where('account_status','!=', 5)
                ->pluck('advisors.id');

            //return $searchAdvisors;
            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($searchAdvisors);

            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            //To get featured
            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);

        }
        if ($request->has('lowerLimitRate') &&
            $request->has('upperLimitRate') &&
            $request->has('lowerLimitReview') &&
            $request->has('upperLimitReview') &&
            $request->has('both')) {

            $searchAdvisors = DB::table('advisors')
                ->whereRaw("timeRate BETWEEN $lowerLimitRate AND $upperLimitRate")
                ->orWhereRaw("feedbackCount BETWEEN $lowerLimitReview AND $upperLimitReview")
                ->where('account_status','!=', 5)
                ->pluck('advisors.id');


            $Advisors = new Advisors();

            $temp = $Advisors->maxRating($searchAdvisors);

            for ($i = 0; $i < count($temp); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $temp[$i]->id)
                    ->avg('rating');

                if ($rating) {
                    // return $rating[1];
                    $temp[$i]->{'rating'} = $rating;
                } else
                    $temp[$i]->{'rating'} = 0;
            }

            //To get featured

            $featured = $Advisors->featured();

            for ($i = 0; $i < count($featured); $i++) {

                $rating = DB::table('advisors_reviews')
                    ->where('advisorId', '=', $featured[$i]->id)
                    ->avg('rating');
                if ($rating) {
                    $featured[$i]->{'rating'} = $rating;
                } else
                    $featured[$i]->{'rating'} = 0;
            }

            $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results', 'All psychics' => $temp, 'featured' => $featured
                , 'blog' => $blogPreviews]);


        }
    }

    public function advisorforgotPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'please provide email', 'Result' => null]);
        }
        $user = Advisors::where('email', '=', $request->input('email'))->first();
        if (is_null($user)) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Email not found in our system', 'Result' => null],400);
        }
        $code = mt_rand(100000, 999999);
        Mail::to($request->input('email'))->send(new PasswordResetNew($user->screenName,$code));
        ResetPasswordModel::insert([
            "user_id" => $user->id,
            "password_code_hash" => Hash::make($code),
            "user_type" => 1
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Reset Password Email Sent. Please Check your Inbox',
            'Result' => null,
            "Verification Code" =>$code]);
    }

    public function validateCode(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required'
        ]);

        if ($validator->fails()) {
            //Getting error messages and sending them in response
            $errorMsg = $validator->messages();
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Error! Parameters Missing', 'Result' => $errorMsg], 422);
        } else {
            $email = $request->get('email');
            $userCode = $request->get('verification_code');
            $advisorData = $user = Advisors::where('email', '=', $email)->first();
            $validateCode = ResetPasswordModel::where('user_id','=',$advisorData->id)->where('user_type',1)->select('password_code_hash')->orderby('created_at','desc')->first();

            if(Hash::check($userCode, $validateCode->password_code_hash)){
                return response()->json(['statusCode' => '1', 'statusMessage' => 'Code Verified Successfully', 'Result' => null]);
            }
            else{
                return response()->json(['statusCode' => '0', 'statusMessage' => 'Verification Code is Wrong', 'Result' => null], 400);
            }
        }
    }

    public function updateAdvisorPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            //Getting error messages and sending them in response
            $errorMsg = $validator->messages();
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Error! Parameters Missing', 'Result' => $errorMsg], 422);
        } else {
            $email = $request->get('email');
            $newPassword = $request->get('new_password');
            $advisorID = Advisors::where('email', '=', $email)->pluck("id");
            Advisors::where('id','=',$advisorID)
                ->update([
                    "password" => Hash::make($newPassword)
                ]);
            ResetPasswordModel::where('user_id','=',$advisorID)->delete();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Your password has been changed. Please Sign in with new password', 'Result' => null]);
        }
    }

    public function addVisitor(Request $request)
    {
        //return $request->ip();
        // $advisorId = $request->input('advisorId');
        $advisorId = input::get('advisorId');

        $oldReviews = DB::table('advisors')
            ->where('id', '=', $advisorId)
            ->pluck('visitor_count')
            ->first();


        $temp = array("advisorId" => $advisorId);

        $lists = DB::table('visitors')->insertGetId($temp);


        DB::table('advisors')->where('id', $advisorId)->update(array('visitor_count' => $oldReviews + 1));


        return response()->json(['statusCode' => '1', 'statusMessage' => 'visitor Added', 'Result' => true], 200);


    }

    public function searchAdvisor(Request $request)
    {

        $legalName = $request->input('legalName');

        $searchAdvisors = \DB::table('advisors')
            ->orWhere('screenName', 'LIKE', '%' . $legalName . '%')
            ->where('account_status','!=', 5)
            ->pluck('advisors.id');

        $Advisors = new Advisors();

        $temp = $Advisors->maxRating($searchAdvisors);

        for ($i = 0; $i < count($temp); $i++) {

            $rating = DB::table('advisors_reviews')
                ->where('advisorId', '=', $temp[$i]->id)
                ->avg('rating');
            if ($rating) {
                $temp[$i]->{'rating'} = $rating;
            } else
                $temp[$i]->{'rating'} = 0;
        }

        $featured = $Advisors->featured();

        for ($i = 0; $i < count($featured); $i++) {

            $rating = DB::table('advisors_reviews')
                ->where('advisorId', '=', $featured[$i]->id)
                ->avg('rating');
            if ($rating) {
                $featured[$i]->{'rating'} = $rating;
            } else
                $featured[$i]->{'rating'} = 0;
        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results', 'All psychics' => $temp, 'featured' => $featured]);

    }

    public function advOrderRecord(Request $request)
    {


        $advisorId = $request->input('advisorId');


        //...................orders Data Calculations


        //.........This Week

        // $thisWeek = \Carbon\Carbon::today()->subDays(7);

        $thisWeek = DB::table('orders')
            // ->where('created_at', '>=', $thisWeek)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('advisorId', '=', $advisorId)
            ->count();

        //...........LAst week

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $lastWeek = DB::table('orders')
            // ->where('created_at', '>=', $thisWeek)
            ->whereBetween('created_at', [$AgoDate, $NowDate])
            ->where('advisorId', '=', $advisorId)
            ->count();

        //................this Month 

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $thisMonth = DB::table('orders')
            ->where(DB::raw('MONTH(created_at)'), '=', date('n'))
            ->where('advisorId', '=', $advisorId)
            ->count();

        //......... Last  Month 

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $lastmonth = DB::table('orders')
            ->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)
            ->where('advisorId', '=', $advisorId)
            ->count();


        //...................Visitors Data Calculations


        //.........This Week

        // $thisWeek = \Carbon\Carbon::today()->subDays(7);

        $VisitorsthisWeek = DB::table('visitors')
            // ->where('created_at', '>=', $thisWeek)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('advisorId', '=', $advisorId)
            ->count();

        //...........Visitors LAst week

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $VisitorslastWeek = DB::table('visitors')
            // ->where('created_at', '>=', $thisWeek)
            ->whereBetween('created_at', [$AgoDate, $NowDate])
            ->where('advisorId', '=', $advisorId)
            ->count();

        //................ Visitors this Month 

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $VisitorsthisMonth = DB::table('visitors')
            ->where(DB::raw('MONTH(created_at)'), '=', date('n'))
            ->where('advisorId', '=', $advisorId)
            ->count();

        //.........Visitors Last  Month 

        $AgoDate = \Carbon\Carbon::now()->subWeek()->format('Y-m-d');
        $NowDate = \Carbon\Carbon::now()->format('Y-m-d');

        $Visitorslastmonth = DB::table('visitors')
            ->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)
            ->where('advisorId', '=', $advisorId)
            ->count();

        $totalvisitorCount = DB::table('advisors')
            ->where('id', '=', $advisorId)
            ->pluck('visitor_count')
            ->first();

        //...........payments related data


        $advisor_credit = DB::table('advisors')
            ->where('id', $advisorId)
            // ->select( 'advisors.advisor_credit')
            ->first();

        if (!$advisor_credit) {

            $paymentHistory = DB::table('payments')
                ->where('advisorId', $advisorId)
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results', 'this_week_orders' => $thisWeek, 'last_week_orders' => $lastWeek, 'this_month_orders' => $thisMonth, 'last_month_orders' => $lastmonth, 'visitors_this_Week' => $VisitorsthisWeek, 'visitors_last_week' => $VisitorslastWeek, 'visitors_this_month' => $VisitorsthisMonth, 'visitors_last_month' => $Visitorslastmonth, 'total_visitors_count' => $totalvisitorCount, 'Advisor_credit' => 0, 'paymentHistory' => $paymentHistory->credit]);

        }
        $paymentHistory = DB::table('payments')
            ->where('advisorId', $advisorId)
            ->orderBy('created_at', 'desc')
            ->first();
        if(empty($paymentHistory)){
            $paymentHistory = 0;
        }
        // return $paymentHistory;

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing results',
            'this_week_orders' => $thisWeek,
            'last_week_orders' => $lastWeek,
            'this_month_orders' => $thisMonth,
            'last_month_orders' => $lastmonth,
            'visitors_this_Week' => $VisitorsthisWeek,
            'visitors_last_week' => $VisitorslastWeek,
            'visitors_this_month' => $VisitorsthisMonth,
            'visitors_last_month' => $Visitorslastmonth,
            'total_visitors_count' => $totalvisitorCount,
            'Advisor_credit' => $advisor_credit->advisor_credit,
            'paymentHistory' => $paymentHistory->credit]);

    }

    public function logout(Request $request)
    {
        $userID = $request->get('advisorId');
        DB::table('advisors')->where('id',$userID)
            ->update([
                "advisorFcmToken" => null,
                "devicePlatform" => null
            ]);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Logout Successfully', 'Result' => null]);
    }

    public function adviserBulkOpt(Request $request){
        $adviserIdData = $request->get('data');
        $OperationType = $request->get('status');

        if(count($adviserIdData) > 0){
            foreach($adviserIdData as $item){
                $advisorDetails = DB::table('advisors')->where('id', $item)->select('screenName','email')->first();
                if($OperationType == 1){
                    Mail::to($advisorDetails->email)->send(new AccountApproved($advisorDetails->screenName));
                }
                else{
                    Mail::to($advisorDetails->email)->send(new AccountRejected($advisorDetails->screenName));
                }
                DB::table('advisors')->where('id', $item)->update(array('account_status' => $OperationType));
            }
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisers Status Changed Successfully', 'Result' => null]);
        }
        else{
            return response()->json(['statusCode' => '1', 'statusMessage' => 'No Data Found', 'Result' => null]);
        }
    }

    public function deactivateAdvisor($id){
        $advDetails = Advisors::find($id);
        $advDetails->account_status = 2;
        $advDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Adviser Deactivated Successfully', 'Result' => null]);
    }

    public function activateAdvisor($id){
        $advDetails = Advisors::find($id);
        $advDetails->account_status = 1;
        $advDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Adviser Activated Successfully', 'Result' => null]);
    }

    public function deleteAdvisor($id){

        $advDetails = Advisors::find($id);

        $randomName = "user".bin2hex(openssl_random_pseudo_bytes(4));
        $advDetails->fullName = $randomName;
        $advDetails->nickName = $randomName;
        $advDetails->screenName = $randomName;
        $advDetails->username = $randomName;
        $advDetails->email = bin2hex(openssl_random_pseudo_bytes(6))."@"."mail.com";
        $advDetails->serviceName = $randomName;
        $advDetails->aboutYourServices = "cvx8VNW8GSZnBXudjtHu asdasdas awewe";
        if(!empty($advDetails->profileImage)){
            $path = base_path() . "/public".$advDetails->profileImage;
            $newFilePath = "/uploads/images/deactivated_user.png";
            File::delete($path);
            $advDetails->profileImage = $newFilePath;
        }
        if(!empty($advDetails->profileVideo)){
            $path = base_path() . "/public".$advDetails->profileVideo;
            File::delete($path);
            $advDetails->profileVideo = null;
        }
        $advDetails->liveChat = 0;
        $advDetails->videoChat = 0;
        $advDetails->instructionForOrder = "cvx8VNW8GSZnBXudjtHu asdasdas awewe";
        $advDetails->legalNameOfIndividual = $randomName;
        $advDetails->dateOfBirth = "1987-11-02";
        $advDetails->country = "nowhere";
        $advDetails->bankDetails = null;
        $advDetails->zipCode = null;
        $advDetails->permanentAddress = null;
        $advDetails->city = null;
        $advDetails->token = null;
        $advDetails->TextChatRate = null;
        $advDetails->timeRate = null;
        $advDetails->paymentThreshold = null;
        $advDetails->TextChatRate = null;
        $advDetails->profileStatus = 0;
        $advDetails->isOnline = 0;
        $advDetails->advisorFcmToken = null;
        $advDetails->isOnline = 0;
        $advDetails->feedbackCount = 0;
        $advDetails->isNotify = 0;
        $advDetails->threeMinuteVideo = 0;
        $advDetails->devicePlatform = 0;
        $advDetails->expirience = null;
        $advDetails->new_status = 0;
        $advDetails->advisor_credit = null;
        $advDetails->language = 0;
        $advDetails->visitor_count = 0;
        $advDetails->account_status = 5;
        $advDetails->is_featured = null;
        $advDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisor Deleted Successfully', 'Result' => null]);

    }

} 