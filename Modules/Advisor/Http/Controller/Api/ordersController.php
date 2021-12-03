<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\Advisor as Advisors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Modules\Advisor\Helper\Utilclass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\resources\emails\mailExample;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Testing\Fakes\MailFake;
use App\config\services;
use App\Orders;
use App\Payments;
use GuzzleHttp\Client;
use Log;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Client as GuzzleHttpClient;
use DB;
// use Excel;

// use Maatwebsite\Excel\Facades\Excel;

class ordersController extends ApiController
{
    // public function __construct()
    //  {
    //      $this->middleware('auth:api', ['except' => ['doLogin', 'store']]);
    //  }

    public function store(Request $request)
    {
        $advisorDetails = Advisors::where('id', $request->advisorId)->first();
        if ($advisorDetails->account_status == 2) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Unable to reply! Account Deactivated', 'Result' => NULL]);
        }
        $costOfTheOrder = 9.99;

        $getBalanceOfUser = DB::table('users')
            ->where('id', '=', $request->userId)
            ->pluck('credit')
            ->first();
        $getBalanceOfAdvisor = DB::table('advisors')
            ->where('id', '=', $request->advisorId)
            ->pluck('advisor_credit')
            ->first();

        // return $getBalanceOfUser;//+9.99;

        if ($getBalanceOfUser < $costOfTheOrder) {

            return response()->json(['statusCode' => '403', 'statusMessage' => 'Recharge your account', 'Result' => NULL], 403);
        }
        $Orders = new Orders();

        if ($request->has('order_video')) {

            $extension = $request->file('order_video')->getClientOriginalExtension();

            $Orders = Orders::create($request->except(['order_video']));

            $unique = bin2hex(openssl_random_pseudo_bytes(15));

            $photo = time() . '-' . $request->file('order_video')->getClientOriginalName();

            $temp = explode('.', $photo);
            $ext = array_pop($temp);
            $name = implode('.mp4', $temp);

            $ext = '.mp4';

            $destination = 'api/public/uploads/';

            $path = $request->file('order_video')->move($destination, $photo . '.' . $ext);

            $Orders->order_video = $path;

            $Orders->save();


            $getuserName = DB::table('users')
                ->where('id', '=', $request->userId)
                //     ->pluck('name')
                ->first();

            $getAdvPltform = DB::table('advisors')
                ->where('id', '=', $request->advisorId)
                ->first();


            ////////////////////////////////////////
            //Creating Notification


            $body = array(
                'body' => $getuserName->name . ' has given a job to you! ',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'sender_id' => $request->userId,
                'type' => "order",
                'badge' => "1",
                'senderName' => $getuserName->name,
                'sound' => 1,
            );
            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $request->advisorId;

            if ($getAdvPltform->devicePlatform == 2) {

                $util->sendPushNotificationToAdvisorAndroid($userID, $title, $body);
            }
            if ($getAdvPltform->devicePlatform == 1) {

                $util->sendPushNotificationToAdvisor($userID, $title, $body);
            }

            ///////////////////////////////////////////


            //  $util = new Utilclass();
            //   $title = 'Your job just came in';
            //   $body = $getuserName.' has given a job to you! ';
            //   $userID =$request->advisorId; 

            //   $util->sendPushNotificationToAdvisor($userID, $title, $body);

            //...Deducting the amout from user and addding in advisor

            DB::table('users')->where('id', $request->userId)->update(array('credit' => $getBalanceOfUser - 9.99));
            $advisorCredit = DB::table('advisors')->where('id', $request->advisorId)->select('advisor_credit')->first();
            DB::table('advisors')->where('id', $request->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor + 9.99 * 0.4));


            //......Create Payments Record of the advisor

            $Payments = new Payments();
            $Payments->userId = $request->userId;
            $Payments->advisorId = $request->advisorId;
            $Payments->order_id = $Orders->id;
            $Payments->credit = 9.99 * 0.4;
            $Payments->admin_credit = 9.99;
            $Payments->system_fee = 9.99 * 0.3;
            $Payments->balance = number_format((float)$advisorCredit->advisor_credit + 9.99 * 0.4, 2, '.', '');
            $Payments->refrence = 'Video Order';
            $Payments->save();


            return response()->json(['statusCode' => '1', 'statusMessage' => 'Order Created', 'Result' => $Orders]);
        } else {
            $advisorCredit = DB::table('advisors')->where('id', $request->advisorId)->select('advisor_credit')->first();
            $temp = $Orders->create($request->except(['order_video']));

            $getuserName = DB::table('users')
                ->where('id', '=', $request->userId)
                ->first();

            $getAdvPltform = DB::table('advisors')
                ->where('id', '=', $request->advisorId)
                ->first();


            ////////////////////////////////////////
            //Creating Notification

            $body = array(
                'body' => $getuserName->name . ' has given a job to you! ',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'sender_id' => $request->userId,
                'type' => "order",
                'badge' => "1",
                'senderName' => $getuserName->name,
                'sound' => 1,
            );

            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $request->advisorId;

            if ($getAdvPltform->devicePlatform == 2) {

                $util->sendPushNotificationToAdvisorAndroid($userID, $title, $body);
            }
            if ($getAdvPltform->devicePlatform == 1) {

                $util->sendPushNotificationToAdvisor($userID, $title, $body);
            }


            ///////////////////////////////////////////


            // $util = new Utilclass();
            // $title = 'Your job just came in';
            // $body = $getuserName.' has given a job to you! ';
            // $userID =$request->advisorId;
            // $util->sendPushNotificationToAdvisor($userID, $title, $body);

            //...Deducting the amout from user and addding in advisor

            DB::table('users')->where('id', $request->userId)->update(array('credit' => $getBalanceOfUser - 9.99));
            DB::table('advisors')->where('id', $request->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor + 9.99 * 0.4));


            //......Create Payments Record of the advisor

            $Payments = new Payments();
            $Payments->userId = $request->userId;
            $Payments->advisorId = $request->advisorId;
            $Payments->order_id = $temp->id;
            $Payments->credit = 9.99 * 0.4;
            $Payments->admin_credit = 9.99;
            $Payments->system_fee = 9.99 * 0.3;
            $Payments->balance = number_format((float)$advisorCredit->advisor_credit + 9.99 * 0.4, 2, '.', '');
            $Payments->refrence = 'Video Order';
            $Payments->save();


            return response()->json(['statusCode' => '1', 'statusMessage' => 'Order Created', 'Result' => $temp]);
        }
    }

    public function update($id, Request $request)
    {

        $advisorDetails = Advisors::where('id', $request->advisorId)->first();
        if ($advisorDetails->account_status == 2) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Unable to reply! Account Deactivated', 'Result' => NULL]);
        }

        $getBalanceOfUser = DB::table('users')
            ->where('id', '=', $request->userId)
            ->pluck('credit')
            ->first();

        $getDevicePlatform = DB::table('users')
            ->where('id', '=', $request->userId)
            ->pluck('devicePlatform')
            ->first();

        $getBalanceOfAdvisor = DB::table('advisors')
            ->where('id', '=', $request->advisorId)
            ->pluck('advisor_credit')
            ->first();


        $Orders = Orders::find($id);
        DB::table('orders')->where('id', $id)->update(array('user_order_seen_status' => 1));
        if ($request->has('reply_Video')) {

            $extension = $request->file('reply_Video')->getClientOriginalExtension();

            $Orders->update($request->except(['reply_Video']));

            $unique = bin2hex(openssl_random_pseudo_bytes(15));

            $photo = time() . '-' . $request->file('reply_Video')->getClientOriginalName();

            $temp = explode('.', $photo);
            $ext = array_pop($temp);
            $name = implode('.mp4', $temp);

            $ext = '.mp4';

            $destination = 'api/public/uploads/';

            $path = $request->file('reply_Video')->move($destination, $photo . '.' . $ext);

            $Orders->reply_Video = $path;

            DB::table('orders')->where('id', $id)->update(array('reply_Video' => $path));
            //  DB::table('orders')->where('id', $id)->update(array('reply_heading' => $request->reply_heading));
            DB::table('orders')->where('id', $id)->update(array('reply_details' => $request->reply_details));
            // DB::table('orders')->where('id', $id)->update(array('readStatus' => $request->readStatus));

            $getuserId = DB::table('orders')
                ->where('orders.id', '=', $id)
                ->pluck('userId')
                ->first();


            $getAdvisorName = DB::table('orders')
                ->where('orders.id', '=', $id)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->pluck('advisors.legalNameOfIndividual')
                ->first();


            ////////////////////////////////////////


            //Creating Notification

            $body = array(
                'body' => $getAdvisorName . ' has given a job response to you! ',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'sender_id' => $request->advisorId,
                'type' => "order",
                'badge' => "1",
                'senderName' => $getAdvisorName,
                'sound' => 1,
            );


            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $getuserId;

            if ($getDevicePlatform == 2) {

                $util->sendPushNotificationToUserAndroid($userID, $title, $body);
            }
            if ($getDevicePlatform == 1) {

                $util->sendPushNotificationToUser($userID, $title, $body);
            }


            ///////////////////////////////////////////


            //       $util = new Utilclass();
            //         $title = 'Response to your job';
            //         $body = $getAdvisorName.' has given a job response to you! ';
            //         $userID =$getuserId;

            //   $util->sendPushNotificationToUser($userID, $title, $body);

            if ($request->has('isCompleted')) {
                if ($request->isCompleted == 3) {

                    DB::table('users')->where('id', $request->userId)->update(array('credit' => $getBalanceOfUser + 9.99));
                    DB::table('advisors')->where('id', $request->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor - 9.99 * 0.4));

                    $paymentsID = DB::table('payments')
                        ->where('order_id', '=', $id)
                        ->select('id')
                        ->first();
                    $paymentDetails = DB::table('payments')
                        ->where('id', '>', $paymentsID->id)
                        ->where('advisorId', $request->advisorId)
                        ->where('userId', $request->userId)
                        ->select('id', 'refrence')
                        ->get();
                    if (count($paymentDetails) > 0) {
                        foreach ($paymentDetails as $item) {
                            $paymentObj = Payments::where('id','=',$item->id)->first();
                            $tempBalance = $paymentObj->balance;
                            if ($tempBalance > 0) {
                                $newBalance = round($tempBalance - 9.99 * 0.4);
                                $paymentObj->balance = $newBalance;
                                $paymentObj->save();
                            }
                        }
                    }
                    DB::table('payments')->where('order_id', $id)->delete();
                }
            }
            return response()->json(['statusCode' => '1', 'statusMessage' => 'successful updated', 'Result' => $Orders]);
        } else {

            $Orders->update($request->except(['reply_Video']));
            DB::table('orders')->where('id', $id)->update(array('user_order_seen_status' => 1));
            $getuserId = DB::table('orders')
                ->where('orders.id', '=', $id)
                ->pluck('userId')
                ->first();

            $getAdvisorName = DB::table('orders')
                ->where('orders.id', '=', $id)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->pluck('advisors.legalNameOfIndividual')
                ->first();

            $getDevicePlatform = DB::table('users')
                ->where('id', '=', $request->userId)
                ->pluck('devicePlatform')
                ->first();

            /////////////////////////////////////////////////////////////

            $body = array(
                'body' => $getAdvisorName . ' has given a job response to you! ',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'sender_id' => $request->advisorId,
                'type' => "order",
                'badge' => "1",
                'senderName' => $getAdvisorName,
                'sound' => 1,
            );

            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $getuserId;

            if ($getDevicePlatform == 2) {

                $util->sendPushNotificationToUserAndroid($userID, $title, $body);
            }
            if ($getDevicePlatform == 1) {

                $util->sendPushNotificationToUser($userID, $title, $body);
            }

            //////////////////////////////////////

            if ($request->has('isCompleted')) {
                if ($request->isCompleted == 3) {

                    DB::table('users')->where('id', $request->userId)->update(array('credit' => $getBalanceOfUser + 9.99));
                    DB::table('advisors')->where('id', $request->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor - 9.99 * 0.4));

                    $paymentsID = DB::table('payments')
                        ->where('order_id', '=', $id)
                        ->select('id')
                        ->first();
                      
                    $paymentDetails = DB::table('payments')
                        ->where('id', '>', $paymentsID->id)
                        ->where('advisorId', $request->advisorId)
                        ->where('userId', $request->userId)
                        ->select('id', 'refrence')
                        ->get();
                    if (count($paymentDetails) > 0) {
                        foreach ($paymentDetails as $item) {
                            $paymentObj = Payments::where('id','=',$item->id)->first();
                            $tempBalance = $paymentObj->balance;
                            if ($tempBalance > 0) {
                                $newBalance = round($tempBalance - 9.99 * 0.4);
                                $paymentObj->balance = $newBalance;
                                $paymentObj->save();
                            }
                        }
                    }
                    DB::table('payments')->where('order_id', $id)->delete();
                }
            }
            return response()->json(['statusCode' => '1', 'statusMessage' => 'successful', 'Result' => $Orders]);
        }
    }

    public function updateOrderStatus(Request $request, $id)
    {

        $advisorId = $request->input('advisorId');
        $userId = $request->input('userId');
        $isCompleted = $request->input('isCompleted');
        $reply_details = $request->input('reply_details');


        DB::table('orders')->where('id', $id)->update(array('isCompleted' => $isCompleted));
        DB::table('orders')->where('id', $id)->update(array('reply_details' => $reply_details));
        DB::table('orders')->where('id', $id)->update(array('user_order_seen_status' => 1));


        $getBalanceOfUser = DB::table('users')
            ->where('id', '=', $request->userId)
            ->pluck('credit')
            ->first();

        $getDevicePlatform = DB::table('users')
            ->where('id', '=', $request->userId)
            ->pluck('devicePlatform')
            ->first();
        // return $getDevicePlatform;
        $getBalanceOfAdvisor = DB::table('advisors')
            ->where('id', '=', $request->advisorId)
            ->pluck('advisor_credit')
            ->first();

        $getuserId = DB::table('orders')
            ->where('orders.id', '=', $id)
            ->pluck('userId')
            ->first();

        $getAdvisorName = DB::table('orders')
            ->where('orders.id', '=', $id)
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->pluck('advisors.legalNameOfIndividual')
            ->first();

        if ($request->isCompleted == 3) {

            DB::table('users')->where('id', $request->userId)->update(array('credit' => $getBalanceOfUser + 9.99));
            DB::table('advisors')->where('id', $request->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor - 9.99 * 0.4));

            DB::table('payments')->where('order_id', $id)->delete();

        }
        //Creating Notification

        $body = array(
            'body' => $getAdvisorName . ' has given a job response to you!',
            'title' => "Thesecretpsychics",
            'vibrate' => 1,
            'sender_id' => $request->advisorId,
            'type' => "order",
            'badge' => "1",
            'senderName' => $getAdvisorName,
            'sound' => 1,
        );
        $util = new Utilclass();
        $title = 'Thesecretpsychics';
        $body = $body;
        $userID = $request->userId;

        if ($getDevicePlatform == 2) {

            $util->sendPushNotificationToUserAndroid($userID, $title, $body);
        }
        if ($getDevicePlatform == 1) {

            $util->sendPushNotificationToUser($userID, $title, $body);
        }


        return response()->json(['statusCode' => '1', 'statusMessage' => 'Order Status updated ', 'Result' => NULL]);
    }

    public function updateOrderSeen(Request $request, $id)
    {

        $isSeen = $request->input('isSeen');

        DB::table('orders')->where('id', $id)->update(array('isSeen' => $isSeen));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Order seen Status updated ', 'Result' => NULL]);
    }


    public function show(Request $request)
    {
        $advisorId = $request->input('advisorId');

        $myUser = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name', 'users.email', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'orders.*')
            ->orderby('orders.created_at','desc')
            ->get();

        $completed_Oders = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->where('order_status', '=', 1)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name', 'users.email', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'orders.*')
            ->orderby('orders.created_at','desc')
            ->get();

        $completed_Oders = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->where('order_status', '=', 1)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name', 'users.email', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'orders.*')
            ->orderby('orders.created_at','desc')
            ->get();

        $recent_oders = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->orderBy('orders.created_at', 'desc')
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name', 'users.email', 'users.gender', 'users.birthday', 'users.specific_information', 'users.phoneNumber', 'orders.*')
            ->orderby('orders.created_at','desc')
            ->get();

        $visitors = DB::table('visitors')
            ->where('advisorId', $advisorId)
            ->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'allOrders' => $myUser, 'completed_Oders' => $completed_Oders, 'recent_oders' => $recent_oders, 'visitors' => $visitors]);
    }


    public function singleOrder($id, Request $request)
    {
        $myUser = DB::table('orders')
            ->where('orders.id', '=', $id)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->select('orders.*', 'users.name', 'users.phoneNumber', 'users.email', 'users.name', 'users.profileImage', 'advisors.screenName', 'advisors.profileImage as advisorImage', 'advisors.liveChat', 'advisors.isOnline', 'advisors.TextChatRate', 'advisors.serviceName')
            ->first();

// return json_encode($myUser);
        $ratingToBeexcl = DB::table('advisors_reviews')
            ->where('advisorId', '=', $myUser->advisorId)
            ->avg('rating');

        if ($ratingToBeexcl) {
            $myUser->{'rating'} = $ratingToBeexcl;
        } else {
            $myUser->{'rating'} = 0;
        }


        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'result' => $myUser]);
    }


    public function showAdvisorOrder(Request $request)
    {
        $advisorId = $request->input('advisorId');
        $userId = $request->input('userId');
        $days = $request->input('days');

        $myUser = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $advisorConn = DB::table('orders')
            ->where('advisorId', $advisorId)
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->join('users', 'users.id', '=', 'orders.userId')
            ->select('users.name as customerName', 'users.id')
            // ->orderBy('orders.created_at', 'desc')
            ->distinct('users.id')
            ->get();

        if ($request->has('advisorId') && $request->has('userId') && $request->has('days')) {

            $string = trim($request->userId, ".");
            $split = explode(",", $string);


            if ($days == 0) {
                $myUser = DB::table('orders')
                    ->where('advisorId', $advisorId)
                    ->whereIn('userId', $split)
                    ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                    ->join('users', 'users.id', '=', 'orders.userId')
                    ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();

                return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'users' => $advisorConn]);
            }

            //.... subtracting the days here

            $date = \Carbon\Carbon::today()->subDays($days);

            $myUser = DB::table('orders')
                ->where('advisorId', $advisorId)
                ->whereIn('userId', $split)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'users' => $advisorConn]);

        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'users' => $advisorConn]);

    }


    public function showUserOrder(Request $request)
    {

        $advisorId = $request->input('advisorId');
        $userId = $request->input('userId');
        $days = $request->input('days');

        DB::table('orders')->where('userId', $userId)->update(array('user_order_seen_status' => 0));

        $myUser = DB::table('orders')
            ->where('userId', '=', $userId)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->orderBy('orders.created_at', 'desc')
            // ->join('categories','categories.id','=','orders.categoryId')
            ->select('orders.*', 'users.name', 'users.phoneNumber', 'users.email', 'users.name', 'users.profileImage', 'advisors.screenName', 'advisors.profileImage as advisorImage', 'advisors.liveChat', 'advisors.isOnline', 'advisors.TextChatRate')
            ->get();

        $usersConn = DB::table('orders')
            ->where('userId', '=', $userId)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            // ->orderBy('orders.created_at','desc')
            // ->join('categories','categories.id','=','orders.categoryId')
            ->select('advisors.screenName', 'advisors.id')
            ->distinct('advisors.id')
            ->get();

        if ($request->has('advisorId') && $request->has('userId') && $request->has('days')) {

            $string = trim($request->advisorId, ".");
            $split = explode(",", $string);

            $date = \Carbon\Carbon::today()->subDays($days);

            if ($days == 0) {

                $myUser = DB::table('orders')
                    ->whereIn('advisorId', $split)
                    ->where('userId', '=', $userId)
                    ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                    ->join('users', 'users.id', '=', 'orders.userId')
                    ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*', 'advisors.screenName', 'advisors.profileImage as advisorImage', 'advisors.liveChat', 'advisors.isOnline', 'advisors.TextChatRate')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();

                return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'advisors' => $usersConn]);

            }

            $myUser = DB::table('orders')
                ->whereIn('advisorId', $split)
                ->where('userId', '=', $userId)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*', 'advisors.screenName', 'advisors.profileImage as advisorImage', 'advisors.liveChat', 'advisors.isOnline', 'advisors.TextChatRate')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'advisors' => $usersConn]);

        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'advisors' => $usersConn]);

    }

    public function showUserOrderWeb(Request $request)
    {

        $userId = $request->input('userId');

        DB::table('orders')->where('userId', $userId)->update(array('user_order_seen_status' => 0));

        $myUser = DB::table('orders')
            ->where('users.email', '=', $userId)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->orderBy('orders.created_at', 'desc')
            // ->join('categories','categories.id','=','orders.categoryId')
            ->select('orders.*', 'users.name', 'users.phoneNumber', 'users.email', 'users.name', 'users.profileImage', 'advisors.screenName', 'advisors.profileImage as advisorImage', 'advisors.liveChat', 'advisors.isOnline', 'advisors.TextChatRate')
            ->get();

        $usersConn = DB::table('orders')
            ->where('users.email', '=', $userId)
            ->join('users', 'users.id', '=', 'orders.userId')
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            // ->orderBy('orders.created_at','desc')
            // ->join('categories','categories.id','=','orders.categoryId')
            ->select('advisors.screenName', 'advisors.id')
            ->distinct('advisors.id')
            ->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser, 'advisors' => $usersConn]);

    }

    public function showAllOrders(Request $request)
    {
        // $type=$request->type;

        if ($request->has('type')) {
            $orders = DB::table('orders')
                ->where('isCompleted', $request->type)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->selectRaw("users.name as customerName,users.email as userEmail,users.gender as userGender,users.birthday as userBirthday,
                users.specific_information as userInformation,users.phoneNumber,users.profileImage as userImage,orders.*,advisors.screenName,
                advisors.profileImage as advisorImage,advisors.liveChat,advisors.isOnline,advisors.TextChatRate,advisors.serviceName,
                (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
                ->orderBy('orders.created_at', 'desc')
                ->paginate(10);
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $orders]);

        }

        $orders = DB::table('orders')
            // ->where('advisorId', $advisorId)
            ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
            ->join('users', 'users.id', '=', 'orders.userId')
            ->selectRaw("users.name as customerName,users.email as userEmail,users.gender as userGender,users.birthday as userBirthday,
                users.specific_information as userInformation,users.phoneNumber,users.profileImage as userImage,orders.*,advisors.screenName,
                advisors.profileImage as advisorImage,advisors.liveChat,advisors.isOnline,advisors.TextChatRate,advisors.serviceName,
                (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")
            ->orderBy('orders.created_at', 'desc')
            ->paginate(10);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $orders]);
    }


    public function orderfilter(Request $request)
    {
        $advisorId = $request->input('advisorId');
        $days = $request->input('days');
        // $fourteen=$request->input('fourteen');
        // $thirty=$request->input('thirty');
        // $sixty=$request->input('sixty');


        if ($days == 7) {
            $date = \Carbon\Carbon::today()->subDays(7);
            $myUser = DB::table('orders')
                ->where('advisorId', $advisorId)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser]);

        }

        if ($days == 14) {
            $date = \Carbon\Carbon::today()->subDays(14);

            $myUser = DB::table('orders')
                ->where('advisorId', $advisorId)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser]);

        }

        if ($days == 30) {
            $date = \Carbon\Carbon::today()->subDays(30);
            $myUser = DB::table('orders')
                ->where('advisorId', $advisorId)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser]);

        }

        if ($days == 60) {

            $date = \Carbon\Carbon::today()->subDays(60);
            $myUser = DB::table('orders')
                ->where('advisorId', $advisorId)
                ->where('orders.created_at', '>=', $date)
                ->join('advisors', 'advisors.id', '=', 'orders.advisorId')
                ->join('users', 'users.id', '=', 'orders.userId')
                ->select('users.name as customerName', 'users.email as userEmail', 'users.gender as userGender', 'users.birthday as userBirthday', 'users.specific_information as userInformation', 'users.phoneNumber', 'users.profileImage as userImage', 'orders.*')
                ->orderBy('orders.created_at', 'desc')
                ->get();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing orders details', 'Result' => $myUser]);

        }


    }
} 