<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\Blogs;
use App\Chats;
use App\ContactUs;
use App\Mail\ClientSupportMessage;
use App\Mail\PasswordReset;
use App\Mail\PasswordResetNew;
use App\Mail\UserMessage;
use App\PasswordReset as ResetPasswordModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\resources\emails\mailExample;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Testing\Fakes\MailFake;
use App\config\services;
use App\User;
use App\ClientDetails;
use App\Orders;
use App\Advisor as Advisors;
use GuzzleHttp\Client;
use Log;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Client as GuzzleHttpClient;
use DB;
// use Excel;
use Maatwebsite\Excel\Facades\Excel;

class userController extends ApiController
{
    // public function __construct()
    //  {
    //      $this->middleware('auth:api', ['except' => ['doLogin', 'store']]);
    //  }

    public function store(Request $request)
    {
        try {

            $array = [];

            $admin = new User();

            $admin->email = $request->input('email');

            if (empty($admin->email)) {
                array_push($array, "email Required");
                //return response()->json(['statusCode'=>'0','statusMessage'=>'Email Required','Result'=>NULL]);
            }

            if (User::where('email', '=', Input::get('email'))->exists()) {
                return response()->json(['statusCode' => '400', 'statusMessage' => 'email Already Exists', 'Result' => NULL]);
            } else {
                //.......generating unique rand id as it was creating issue in chating between client and advisor

                $id = mt_rand(10, 99);
                $admin = new User();
                $admin->id = $id;
                $admin->email = $request->input('email');
                $admin->password = Hash::make(Input::get("password"));
                $admin->name = $request->input('name');
                $admin->userFcmToken = $request->input('userFcmToken');
                $admin->new_status = $request->input('new_status');
                $admin->language = $request->input('language');
                $admin->account_status = 1;

                if (!$admin->password) {
                    array_push($array, "Password Required");
                }
                if (count($array) > 0) {

                    return response()->json(['statusCode' => '400', 'statusMessage' => 'Fill the given fields', 'Result' => $array]);
                }

                if ($request->has('profileImage')) {
                    $format = '.png';
                    $entityBody = $request['profileImage'];// file_get_contents('php://input');
                    $imageName = $admin->id . time() . '-' . $format;

                    $directory = "/images/profileImages/";
                    $path = base_path() . "/public" . $directory;
                    $data = base64_decode($entityBody);

                    file_put_contents($path . $imageName, $data);

                    $response = $directory . $imageName;

                    $admin->profileImage = $response;
                }
                $admin->save();

                $token = bin2hex(openssl_random_pseudo_bytes(25));

                DB::table('users')->where('id', $admin->id)->update(array('token' => $token));

                $admin->token = $token;

                // Mail::send('emails.verify',["data" => $admin], function($message) use ($admin) {
                // $message->from('baqalah1@gmail.com', 'FUSE');
                // $message->to($admin->email, 'Mail Confirmation')->subject('Testing Mail');
                //         });

                $collection = collect(['id' => $id, 'email' => $admin->email, 'token' => $admin->token, 'name' => $admin->name, 'profileImage' => $admin->profileImage]);
                $collection->toJson();
                return response()->json(['statusCode' => '200', 'statusMessage' => 'Account Successfully Created', 'Result' => $collection]);

            }
        } catch (\Exception $e) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        }
    }

    public function confirm(Request $request, $id)
    {

        $admin = User::where('token', '=', $id)->first();
        if (!$admin) {
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Something Went wrong', 'Result' => $admin]);
        } else {
            $account_status = 1;
            DB::table('users')->where('token', $id)->update(array('account_status' => $account_status));

            $admin->save();

            return redirect('http://dr-romia.com/#/pages/auth/login-2');
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    public function doLogin(Request $request)
    {

        $token = "";
        $Email = Input::get("email");
        $userFcmToken = Input::get("userFcmToken");
        $devicePlatform = Input::get("devicePlatform");
        // $Password = Input::get("password"); 
        $Password = Hash::make(Input::get("password"));

        // $check_User = DB::table('users')
        //     ->select()
        //     ->where('email', '=', $Email)
        //     ->where('password', '=', $Password) 
        //     ->first();

        $credentials = $request->only('email', 'password');
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Email or password wrong', 'Result' => NULL]);
        }

        if (!$token) {
            return response()->json(['statusCode' => '400', 'statusMessage' => 'Incorrect Email or Password', 'Result' => NULL]);
        }

        // $token = bin2hex(openssl_random_pseudo_bytes(25));

        // $myUser = DB::table('users')
        //     ->select()
        //     ->where('email', '=', $Email)
        //     ->where('password', '=', $Password)
        //     ->first();
        $myUser = Auth::user();
        // $token = $myUser->token;
        // return $myUser;
        // if ($myUser->account_status == 0) 
        // {
        //  return response()->json(['statusCode' => '403', 'statusMessage' => 'Kindly Verify Your Email', 'Result' => NULL]);
        // } 

        if ($myUser) {

            $myUser->token = $token;

            DB::table('users')->where('email', $Email)->update(array('token' => $token));
            DB::table('users')->where('email', $Email)->update(array('userFcmToken' => $userFcmToken));
            DB::table('users')->where('email', $Email)->update(array('devicePlatform' => $devicePlatform));

            return response()->json(['statusCode' => '200', 'statusMessage' => 'Logged In', 'Result' => $myUser]);
        } else {
            return response()->json(['statusCode' => '400', 'statusMessage' => 'Email or Password Incorrect', 'Result' => NULL]);
        }
    }

    public function logout(Request $request)
    {
        $userID = $request->get('userId');
        DB::table('users')->where('id', $userID)
            ->update([
                "userFcmToken" => null,
                "devicePlatform" => null
            ]);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Logout Successfully', 'Result' => null]);
    }

    public function notFound()
    {
        return response()->json(['statusCode' => '404', 'statusMessage' => 'Something Went Wrong', 'Result' => NULL]);
    }

    public function updatePassWord(Request $request)
    {
        $email = $request->input('email');

        $token = $request->input('token');

        $password = $request->input('password');

        $up = DB::table('users')->where('email', '=', $email)->where('token', '=', $token)->update(array('password' => Hash::make(Input::get("password"))));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Password is updated', 'Result' => $up]);
    }

    public function getuserInfo(Request $request)
    {
        $userId = $request->input('userId');

        $up = DB::table('users')
            ->where('users.id', '=', $userId)
            ->join('orders', 'orders.userId', '=', 'users.id')
            ->join('categories', 'categories.id', '=', 'orders.categoryId')
            ->select('users.name', 'users.email', 'users.account_status', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'categories.categoryName', 'orders.created_at')
            ->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing User Info', 'Result' => $up]);
    }


    public function userInfo(Request $request, $id)
    {

        $up = DB::table('users')
            ->where('users.id', '=', $id)
            ->first();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing User Info', 'Result' => $up]);
    }


    public function showPsychics(Request $request)
    {

        $userId = $request->input('userId');

        $Advisors = new Advisors();

        $psychics = $Advisors->getipc();

        for ($i = 0; $i < count($psychics); $i++) {

            $rating = DB::table('advisors_reviews')
                ->where('advisorId', '=', $psychics[$i]->id)
                ->avg('rating');

            if ($rating) {
                // return $rating[1];
                $psychics[$i]->{'rating'} = $rating;
            } else
                $psychics[$i]->{'rating'} = 0;
        }

        $featured = $Advisors->featured();
        // return $featured;
        for ($i = 0; $i < count($featured); $i++) {

            $rating = DB::table('advisors_reviews')
                ->where('advisorId', '=', $featured[$i]->advisorId)
                ->avg('rating');
            if ($rating) {
                $featured[$i]->{'rating'} = $rating;
            } else
                $featured[$i]->{'rating'} = 0;
        }

        $favAdvisor = DB::table('favourite_advisors')
            ->where('userId', '=', $userId)
            ->pluck('advisorId');

        $blogPreviews = Blogs::select('id','blog_title','blog_image')->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing Psychics', 'All psychics' => $psychics,
            'featured' => $featured, 'favAdvisors' => $favAdvisor, 'blog' => $blogPreviews]);
    }

    public function update($id, Request $request)
    {
        $User = User::find($id);

        if (!$User) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        // $VendorProfiles->update($request->all());
        if ($request->has('profileImage')) {

            $User->update($request->except(['profileImage', 'token', 'email']));

            $unique = bin2hex(openssl_random_pseudo_bytes(11));

            $format = '.png';

            $entityBody = $request['profileImage'];// file_get_contents('php://input');

            $imageName = $User->id . $unique . $format;

            $directory = "/images/profileImages/";

            $path = base_path() . "/public" . $directory;

            $data = base64_decode($entityBody);

            file_put_contents($path . $imageName, $data);

            $response = $directory . $imageName;

            $User->profileImage = $response;

            DB::table('users')->where('id', $id)->update(array('profileImage' => $response));
            DB::table('users')->where('id', $id)->update(array('new_status' => 1));
        }
        else {

            $User->update($request->except(['profileImage', 'token', 'email']));

            DB::table('users')->where('id', $id)->update(array('new_status' => 1));

            $User = DB::table('users')->where('id', $id)->first();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'user data is Updated', 'Result' => $User]);

        }
        $User = DB::table('users')->where('id', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'user data is Updated', 'Result' => $User]);

    }

    public function updateUser(Request $request)
    {
        $User = User::where('email', $request->get('oldEmail'))
            ->where('name', $request->get('oldName'))
            ->first();

        if (count($User) == 0) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        // $VendorProfiles->update($request->all());
        if ($request->has('profileImage')) {

            $User->update($request->except(['profileImage', 'token', 'oldEmail','oldName']));

            $previousImage = $User->profileImage;
            if(!empty($previousImage)){
                $path = base_path() . "/public" . $previousImage;
                File::delete($path);
            }

            $unique = bin2hex(openssl_random_pseudo_bytes(11));

            $format = '.png';

            $entityBody = $request['profileImage'];// file_get_contents('php://input');

            $imageName = $User->id . $unique . $format;

            $directory = "/images/profileImages/";

            $path = base_path() . "/public" . $directory;

            $data = base64_decode($entityBody);

            file_put_contents($path . $imageName, $data);

            $response = $directory . $imageName;

            $User->profileImage = $response;

            DB::table('users')->where('email', $request->get('email'))->update(array('profileImage' => $response));
            DB::table('users')->where('email', $request->get('email'))->update(array('new_status' => 1));
        } else {

            $User->update($request->except(['profileImage', 'token', 'oldEmail','oldName']));

            DB::table('users')->where('email', $request->get('email'))->update(array('new_status' => 1));

            $User = DB::table('users')->where('email', $request->get('email'))->first();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'user data is Updated', 'Result' => $User]);

        }
        $User = DB::table('users')->where('email', $request->get('email'))->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'user data is Updated', 'Result' => $User]);

    }

    public function External_Login(Request $request)
    {
        //Get Input parameters from the service URL

        $email = $request->input('email');
        if (empty($email)) {

            return response()->json(['statusCode' => '1', 'statusMessage' => 'email is Required', 'Result' => NULL]);

        }
        $token = $request->input('token');
        $fullName = $request->input('fullName');


        $myUser = '';
        //If user does not exiSt, create the user
        if (!(User::where('email', '=', $email)->exists())) {
            $admin = new User();

            $id = mt_rand(1000000000000000, 999999999999999999);
            $admin->id = $id;
            // $admin->username =$request->input('username');
            $admin->email = $request->input('email');
            $admin->token = bin2hex(openssl_random_pseudo_bytes(25));
            $admin->password = bin2hex(openssl_random_pseudo_bytes(25));
            // $admin->provider =$request->input('provider');
            $admin->name = $request->input('name');
            $admin->profileImage = $request->input('profileImage');
            $admin->userFcmToken = $request->input('userFcmToken');
            $admin->account_status = 1;
            $admin->new_status = 1;

            if ($request->has('profileImage')) {
                $format = '.png';
                $entityBody = $request['profileImage'];// file_get_contents('php://input');
                $imageName = $admin->id . time() . '-' . $format;

                $directory = "/images/profileImages/";
                $path = base_path() . "/public" . $directory;
                $data = base64_decode($entityBody);

                file_put_contents($path . $imageName, $data);

                $response = $directory . $imageName;

                $admin->profileImage = $response;
            }
            $admin->save();
        } else {
            $myUser = DB::table('users')
                ->select('id', 'email', 'token')
                ->where('email', '=', $email)
                ->first();
        }
        if ($myUser) {
            $token = bin2hex(openssl_random_pseudo_bytes(25));
            $myUser->token = $token;
            DB::table('users')->where('email', $email)->update(array('token' => $token));
            DB::table('users')->where('email', $email)->update(array('userFcmToken' => $request->input('userFcmToken')));
            DB::table('users')->where('email', $email)->update(array('devicePlatform' => $request->input('devicePlatform')));
            $temp = DB::table('users')->where('email', $email)->first();

            return response()->json(['statusCode' => '200', 'statusMessage' => 'Logged In', 'Result' => $temp]);
        }
        $temp = DB::table('users')->where('email', $email)->first();

        return response()->json(['statusCode' => '200', 'statusMessage' => 'Logged In', 'Result' => $temp]);
    }

    public function userforgotPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Please provide your email', 'Result' => null]);
        }
        $user = User::where('email', '=', $request->input('email'))->first();
        if (is_null($user)) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Email not found in our system', 'Result' => null]);
        }

        $code = mt_rand(100000, 999999);
        Mail::to($request->input('email'))->send(new PasswordResetNew($user->name,$code));
        ResetPasswordModel::insert([
            "user_id" => $user->id,
            "password_code_hash" => Hash::make($code),
            "user_type" => 2
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
            $ClientData = User::where('email', '=', $email)->first();
            $validateCode = ResetPasswordModel::where('user_id','=',$ClientData->id)->where('user_type',2)->select('password_code_hash')->orderby('created_at','desc')->first();

            if(Hash::check($userCode, $validateCode->password_code_hash)){
                return response()->json(['statusCode' => '1', 'statusMessage' => 'Code Verified Successfully', 'Result' => null]);
            }
            else{
                return response()->json(['statusCode' => '0', 'statusMessage' => 'Verification Code is Wrong', 'Result' => null], 400);
            }
        }
    }

    public function updateUserPassword(Request $request){
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
            $userID = User::where('email', '=', $email)->pluck("id");
            User::where('id','=',$userID)
                ->update([
                    "password" => Hash::make($newPassword)
                ]);
            ResetPasswordModel::where('user_id','=',$userID)->delete();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Your password has been changed. Please Sign in with new password', 'Result' => null]);
        }
    }

    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'user_type' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            //Getting error messages and sending them in response
            $errorMsg = $validator->messages();
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Error! Parameters Missing', 'Result' => $errorMsg], 422);
        } else {
            $userID = $request->get('user_id');
            $currentPass = $request->get('current_password');
            $userType = $request->get('user_type');
            $newPass = $request->get('new_password');
            if($userType == 1){
                $oldPassword = Advisors::where('id', '=', $userID)->pluck('password')->first();

                if (Hash::check($currentPass, $oldPassword)) {
                    Advisors::where('id', '=', $userID)
                        ->update([
                            "password" => Hash::make($newPass)
                        ]);
                    return response()->json(['statusCode' => '1', 'statusMessage' => 'Your password has been changed. Please Sign in again ', 'Result' => null]);
                } else {
                    return response()->json(['statusCode' => '0', 'statusMessage' => 'Current Password is Wrong', 'Result' => null], 400);
                }
            }
            else{
                $oldPassword = User::where('id', '=', $userID)->pluck('password')->first();

                if (Hash::check($currentPass, $oldPassword)) {
                    User::where('id', '=', $userID)
                        ->update([
                            "password" => Hash::make($newPass)
                        ]);
                    return response()->json(['statusCode' => '1', 'statusMessage' => 'Password Changed Successfully', 'Result' => null]);
                } else {
                    return response()->json(['statusCode' => '0', 'statusMessage' => 'Current Password is Wrong', 'Result' => null], 400);
                }
            }
        }
    }

    public function addCredit(Request $request)
    {
        $userId = $request->input('userId');
        $credit = $request->input('credit');
        $promo_code = $request->input('promo_code');
        $oldCredit = DB::table('users')
            ->where('id', '=', $userId)
            ->pluck('credit')
            ->first();

        $newCredit = $oldCredit + $request->input('credit');

        DB::table('users')->where('id', $userId)->update(array('credit' => $newCredit));
        $newCredit = DB::table('users')
            ->where('id', '=', $userId)
            ->first();


        if ($credit == 55) {

            $actualAmount = 55 - 5;

            $temp = array("userId" => $request->input('userId'), "percentage" => 10, "actualAmount" => $actualAmount, "amount" => $actualAmount * 0.1);

            $lists = DB::table('cashbacks')->insertGetId($temp);

        }
        if ($credit == 110) {
            $actualAmount = 110 - 10;
            $temp = array("userId" => $request->input('userId'), "percentage" => 10, "actualAmount" => $actualAmount, "amount" => $actualAmount * 0.1);

            $lists = DB::table('cashbacks')->insertGetId($temp);

        }
        if ($request->has('promo_code')) {
            $checkTypeOfPRomoCode = DB::table('promo_codes')
                ->where('promo_code', '=', $promo_code)
                ->first();

            if ($checkTypeOfPRomoCode->allowed_multiple_times == 'once') {

                $checkIfAlreadyApplied = DB::table('applied_pomo_codes')
                    ->where('userId', '=', $userId)
                    ->where('promo_code', '=', $promo_code)
                    ->first();

                if ($checkIfAlreadyApplied) {
                    return response()->json(['statusCode' => '0', 'statusMessage' => 'You have already Applied this Promo Code', 'Result' => NULL]);
                }

                $temp = array("userId" => $request->input('userId'), "promo_code" => $promo_code);

                $lists = DB::table('applied_pomo_codes')->insertGetId($temp);

            }
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Amount updated', 'Result' => $newCredit]);
    }

    //Commented
    /*
      public function addCredit(Request $request)
             {
                 $userId = $request->input('userId');
                 $credit =   $request->input('credit');
                   $oldCredit = DB::table('users')
                        ->where('id', '=', $userId)
                        ->pluck('credit')
                        ->first();

                      $newCredit = $oldCredit+$request->input('credit');

                      DB::table('users')->where('id', $userId)->update(array('credit' =>$newCredit));
                         $newCredit = DB::table('users')
                                ->where('id', '=', $userId)
                                 ->first();


               if ($credit == 55) {
                  $actualAmount = 55-5;

                     $temp = array("userId" => $request->input('userId'), "percentage" =>10,"actualAmount" =>$actualAmount,"amount" =>$actualAmount*0.1);

                        $lists =  DB::table('cashbacks')->insertGetId($temp);

                           }
               if ($credit == 110) {

                     $actualAmount = 110-10;
                    // return $actualAmount*0.1;

                       $temp = array("userId" => $request->input('userId'), "percentage" =>10,"actualAmount" =>$actualAmount,"amount" =>$actualAmount*0.1);

                        $lists =  DB::table('cashbacks')->insertGetId($temp);

                           }
              return response()->json(['statusCode' => '1', 'statusMessage' => 'Amount updated', 'Result' => $newCredit]);
             }
    */
    public function getCashback(Request $request)
    {
        $userId = $request->input('userId');


        $checkCashback = DB::table('cashbacks')
            ->where('userId', '=', $userId)
            ->where('cashback_status', '=', 0)
            ->orderBy('created_at', 'desc')
            ->pluck('amount')
            ->first();

        $response = DB::table('cashbacks')
            ->where('userId', '=', $userId)
            ->where('cashback_status', '=', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$checkCashback) {
            return response()->json(['statusCode' => '403', 'statusMessage' => 'No CashbackAvailable', 'Result' => NULL]);
        }

        $oldCredit = DB::table('users')
            ->where('id', '=', $userId)
            ->pluck('credit')
            ->first();

        $newCredit = $oldCredit + $checkCashback;
        // return $newCredit;
        DB::table('users')->where('id', $userId)->update(array('credit' => $newCredit));
        DB::table('cashbacks')->where('userId', $userId)->update(array('cashback_status' => 1));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Amount updated', 'Result' => $response]);
    }


    public function switch(Request $request)
    {
        $email = $request->input('email');

        $switch = $request->input('switch');

        $userData = DB::table('users')
            ->where('email', '=', $email)
            ->first();

        $advisorData = DB::table('advisors')
            ->where('email', '=', $email)
            ->first();

        // return json_encode($userData);

        if ($switch == 'user') {

            $users = User::where('email', '=', $email)
                        ->first();

            if (empty($users)) {

                // $id = mt_rand(1000000000000000, 999999999999999999);

                $User = new User();
                // $User->id = $id;
                $User->email = $advisorData->email;
                $User->password = $advisorData->password;
                $User->save();

                $usersInfo = User::where('id', '=', $User->id)
                                ->first();
                $accessToken = $usersInfo->createToken('Agent Password Grant Token')->accessToken;
                $response  = [
                            'statusCode' => '1',
                            'statusMessage' => 'Logged In',
                            'token' => $accessToken,
                            'Result'  => $usersInfo
                        ];
                return response()->json($response);
            } else {

                // $token = bin2hex(openssl_random_pseudo_bytes(25));
                // DB::table('users')
                //     ->where('email', $email)
                //     ->update(array('token' => $token));
                $accessToken = $users->createToken('Client Password Grant Token')->accessToken;
                $response  = [
                            'statusCode' => '1',
                            'statusMessage' => 'Logged In',
                            'token' => $accessToken,
                            'Result'  => $users
                        ];
                return response()->json($response);
            }
        }

        if ($switch == 'advisor') {

            $advisors = Advisors::where('email', '=', $email)
                                ->first();

            if (empty($advisors)) {

                $temp = array("email" => $userData->email, "password" => $userData->password, "liveChat" => 1,
                    "videoChat" => 1, "isOnline" => 1);
                // $ists = Advisors::create($temp);
                // $lists = Advisors::where('id',$ists->id)->first();
                $lists = DB::table('advisors')->insertGetId($temp);

                $advisorInfo = Advisors::where('id', '=', $lists)
                                        ->first();
                $accessToken = $advisorInfo->createToken('Agent Password Grant Token')->accessToken;
                $response  = [
                            'statusCode' => '1',
                            'statusMessage' => 'Logged In',
                            'token' => $accessToken,
                            'Result'  => $advisorInfo
                        ];
                return response()->json($response);
            } else {
                $accessToken = $advisors->createToken('Agent Password Grant Token')->accessToken;
                $response  = [
                            'statusCode' => '1',
                            'statusMessage' => 'Logged In',
                            'token' => $accessToken,
                            'Result'  => $advisors
                        ];
                return response()->json($response);
            }
        }
        return response()->json(['statusCode' => '400', 'statusMessage' => 'please follow the signup process', 'Result' => NULL]);
    }

    public function getCredit(Request $request)
    {
         
        $userId = $request->input('userId');
        $type = $request->input('type');

        if ($type == 2) {

            $credit = DB::table('users')
                ->where('id', '=', $userId)
                ->pluck('credit')
                ->first();

            $chat_counter = DB::table('inbox_view_user')
                ->where('sentTo', '=', $userId)
                ->orwhere('sentBy', '=', $userId)
                ->sum('chat_counter');

            $new_reply = DB::table('orders')
                ->where('userId', '=', $userId)
                ->where('user_order_seen_status', '=', 1)
                ->sum('user_order_seen_status');


            return response()->json(['statusCode' => '1', 'statusMessage' => 'shownig Information', 'credit' => $credit, 'chat_counter' => $chat_counter, 'new_reply' => $new_reply], 200);
        }

        if ($type == 1) {

            $chat_counter = DB::table('inbox_view_advisor')
                ->where('sentBy', '=', $userId)
                ->orwhere('sentTo', '=', $userId)
                ->sum('chat_counter');

            $pending_orders = DB::table('orders')
                ->where('advisorId', '=', $userId)
                ->where('isCompleted', '=', 0)
                ->count();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'shownig Information', 'chat_counter' => $chat_counter, 'pending_orders' => $pending_orders], 200);
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'shownig Information', 'Result' => NULL], 200);


    }

    public function Contact_Us()
    {
        $firstName = Input::get('first_name');
        $lastName = Input::get('last_name');
        $Email = Input::get('email');
        $subject = Input::get('subject');

        ContactUs::insert([
            "first_name" => $firstName, "last_name" => $lastName,
            "email" => $Email,"message" => $subject,
        ]);

        Mail::to('support@thesecretpsychics.com')->send(new UserMessage($Email, $subject, $firstName.' '.$lastName));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Your Email Has Been Sent,Thanks For Your Support', 'Result' => NULL], 200);


    }

    public function dashboard()
    {

        $orders['total_orders'] = Orders::count();
        $orders['completed_orders'] = Orders::where('isCompleted', 1)->count();
        $orders['pending_orders'] = Orders::where('isCompleted', 0)->count();
        $orders['decline_orders'] = Orders::where('isCompleted', 3)->count();
        $orders['flagged_orders'] = Orders::where('isCompleted', 4)->count();
        $orders['total_advisors'] = Advisors::where('profileStatus', 1)->count();
        $orders['pending_advisors'] = Advisors::where('account_status',0)->where('profileStatus', 1)->count();
        $orders['approved_advisors'] = Advisors::where('account_status',1)->where('profileStatus', 1)->count();
        $orders['deactivated_advisors'] = Advisors::where('account_status',2)->count();
        $orders['active_users'] = User::where('account_status',1)->count();
        $orders['deactivated_users'] = User::where('account_status',0)->count();
        $orders['users'] = User::orderby('created_at', 'desc')->where('account_status','!=','2')->paginate(10);
        $orders['advisors'] = Advisors::selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
            ->where('account_status','!=','5')->orderby('created_at', 'desc')->paginate(10);


        return response()->json(['statusCode' => '1', 'statusMessage' => 'Your Email Has Been Sent,Thanks For Your Support', 'Result' => $orders], 200);
    }

    public function getUsers()
    {
        $userDetails = User::where('account_status','=',1)->orderby('created_at', 'desc')->paginate(10);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Activated Users', 'Result' => $userDetails], 200);
    }

    public function getDeactivatedUsers()
    {
        $userDetails = User::where('account_status','=',0)->orderby('created_at', 'desc')->paginate(10);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Deactivated Users', 'Result' => $userDetails], 200);
    }

    public function getSingleUser(Request $request)
    {
        $up = User::join('orders', 'orders.userId', '=', 'users.id')
            ->join('categories', 'categories.id', '=', 'orders.categoryId')
            ->select('users.name', 'users.email', 'users.account_status', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'categories.categoryName', 'orders.created_at')
            ->where('email','=', $request->get('email'))
            ->where('name', '=',$request->get('name'))
            ->get();

        if(count($up) > 0){
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Users', 'Result' => $up], 200);
        }
        else{
           $userDetails =  User::where('email','=', $request->get('email'))
                ->where('name', '=',$request->get('name'))
                ->get();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Users', 'Result' => $userDetails], 200);
        }
    }

    public function deactivateUser(Request $request){
        $userID = $request->get('userID');
        $userDetails = User::find($userID);
        $userDetails->account_status = 0;
        $userDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'User Deactivated Successfully', 'Result' => null]);
    }

    public function activateUser(Request $request){
        $userID = $request->get('userID');
        $userDetails = User::find($userID);
        $userDetails->account_status = 1;
        $userDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'User Activated Successfully', 'Result' => null]);
    }

    public function deleteUser(Request $request){
        $userID = $request->get('userID');
        $userDetails = User::find($userID);
        $userDetails->email = bin2hex(openssl_random_pseudo_bytes(6))."@"."mail.com";
        $userDetails->name = "user".bin2hex(openssl_random_pseudo_bytes(6));
        $userDetails->account_status = 2;
        if(!empty($userDetails->profileImage)){
            $path = base_path() . "/public".$userDetails->profileImage;
            $newFilePath = "/uploads/images/deactivated_user.png";
            File::delete($path);
            $userDetails->profileImage = $newFilePath;
        }
        $userDetails->token = null;
        $userDetails->credit = null;
        $userDetails->userFcmToken = null;
        $userDetails->language = null;
        $userDetails->devicePlatform = null;
        $userDetails->new_status = null;
        $userDetails->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'User Deleted Successfully', 'Result' => null]);
    }




}