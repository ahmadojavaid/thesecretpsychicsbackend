<?php

namespace Modules\Client\Http\Controller\Api;

use App\AdvisorsReviews;
use App\Conversation;
use App\Orders;
use App\User;
use App\Chats;
use App\Advisor as Advisors;
use App\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;


class chatController extends ApiController
{
    //   public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    //..........Department 

    public function store(Request $request)
    {
        $chatID = 0;
        if ($request->type == 1) {
            $advisorDetails = Advisors::where('id', $request->sentBy)->first();
            if ($advisorDetails->account_status == 2) {
                return response()->json(['statusCode' => '0', 'statusMessage' => 'Unable to send message! Account Deactivated', 'Result' => NULL]);
            }
            //checking the balance of the user and number of amount to be deducted

            if ($request->has('deductionCount')) {

                $currentAdvisorCredit = DB::table('advisors')
                    ->where('id', '=', $request->sentTo)
                    ->pluck('advisor_credit')
                    ->first();


                $getChatRateOfTheAdvisor = DB::table('advisors')
                    ->where('id', '=', $request->sentTo)
                    ->pluck('TextChatRate')
                    ->first();


                $costToBeDeducted = $getChatRateOfTheAdvisor * $request->deductionCount;

                $BalanceOfUser = DB::table('users')
                    ->where('id', '=', $request->sentBy)
                    ->pluck('credit')
                    ->first();

                if ($BalanceOfUser < $costToBeDeducted) {
                    return response()->json(['statusCode' => '403', 'statusMessage' => 'You have insufficient Balance', 'Result' => NULL], 403);
                }
            }

            $conversationID = Conversation::updateOrCreate(["user_id" => $request->sentTo, "advisor_id" => $request->sentBy],[
                "user_id" => $request->sentTo, "advisor_id" => $request->sentBy, "updated_at" => \Illuminate\Support\Carbon::now()
            ]);
            $conversationID->updated_at = \Illuminate\Support\Carbon::now();
            $conversationID->save();

            $Chats = Chats::create($request->all());
            $Chats->conversation_id = $conversationID->id;
            $Chats->save();
            $chatID = $Chats->id;
        }

        if($request->type == 2){
            //checking the balance of the user and number of amount to be deducted

            if ($request->has('deductionCount')) {

                $currentAdvisorCredit = DB::table('advisors')
                    ->where('id', '=', $request->sentTo)
                    ->pluck('advisor_credit')
                    ->first();


                $getChatRateOfTheAdvisor = DB::table('advisors')
                    ->where('id', '=', $request->sentTo)
                    ->pluck('TextChatRate')
                    ->first();


                $costToBeDeducted = $getChatRateOfTheAdvisor * $request->deductionCount;

                $BalanceOfUser = DB::table('users')
                    ->where('id', '=', $request->sentBy)
                    ->pluck('credit')
                    ->first();

                if ($BalanceOfUser < $costToBeDeducted) {
                    return response()->json(['statusCode' => '403', 'statusMessage' => 'You have insufficient Balance', 'Result' => NULL], 403);
                }
            }
            $conversationID = Conversation::updateOrCreate(["user_id" => $request->sentBy, "advisor_id" => $request->sentTo],[
                "user_id" => $request->sentBy, "advisor_id" => $request->sentTo, "updated_at" => \Illuminate\Support\Carbon::now()
            ]);
            $conversationID->updated_at = \Illuminate\Support\Carbon::now();
            $conversationID->save();

            $permitted_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $stamp = substr(str_shuffle($permitted_chars), 0, 10);
            $Chats = Chats::create($request->all());
            $Chats->conversation_id = $conversationID->id;
            $Chats->msg_stamp = $stamp;
            $Chats->save();
            $chatID = $Chats->id;
        }



        // $Chats = Chats::create($request->all());

        $Chats = new Chats();
        $conversationID = 0;


        if ($request->type == 2) {
            DB::table('advisors')
                ->where('id', '=', $request->sentTo)
                ->pluck('liveChat')
                ->first();

            $isChatEnableOfTheAdvisor = DB::table('advisors')
                ->where('id', '=', $request->sentTo)
                ->pluck('liveChat')
                ->first();

            $advisorName = DB::table('advisors')
                ->where('id', '=', $request->sentTo)
                ->pluck('screenName')
                ->first();
            // return $isChatEnableOfTheAdvisor;

            if ($isChatEnableOfTheAdvisor == 0) {

                return response()->json(['statusCode' => '406', 'statusMessage' => $advisorName.' is not available right now, please check back later', 'Result' => NULL], 406);
            }
        }


        // Deduction and amount transferring

        DB::table('users')->where('id', $request->sentBy)->update(array('credit' => $BalanceOfUser - $costToBeDeducted));
        $advisorCredit = DB::table('advisors')->where('id', $request->sentTo)->select('advisor_credit')->first();
        DB::table('advisors')->where('id', $request->sentTo)->update(array('advisor_credit' => $currentAdvisorCredit + $costToBeDeducted * 0.4));

        DB::table('inbox_view_user')
            ->where('sentTo', '=', $request->sentTo)
            ->where('sentBy', '=', $request->sentBy)
            ->delete();

        DB::table('inbox_view_user')
            ->where('sentTo', '=', $request->sentBy)
            ->where('sentBy', '=', $request->sentTo)
            ->delete();


        DB::table('inbox_view_advisor')
            ->where('sentTo', '=', $request->sentTo)
            ->where('sentBy', '=', $request->sentBy)
            ->delete();

        DB::table('inbox_view_advisor')
            ->where('sentTo', '=', $request->sentBy)
            ->where('sentBy', '=', $request->sentTo)
            ->delete();


        $sentBy = $request->sentBy;
        $sentTo = $request->sentTo;


        $counter = Chats::where(function ($query) use ($sentBy, $sentTo) {
            $query->where('sentBy', $sentBy)
                ->Where('chatStatus', 0);
        })
            ->orWhere(function ($query) use ($sentBy, $sentTo) {
                $query->where('sentTo', $sentBy)
                    ->where('chatStatus', 0);
            })->count();


        // $counter = DB::table('chats') 
        //     ->where('sentTo', '=', $request->sentTo) 
        //     ->where('sentBy', '=', $request->sentBy)

        //     ->delete(); 


        $temp = array("sentTo" => $request->sentTo, 'sentBy' => $request->sentBy, 'message' => $request->message);

        $inbox_view_advisor = DB::table('inbox_view_advisor')->insertGetId($temp);

        $temp = array("sentTo" => $request->sentTo, 'sentBy' => $request->sentBy, 'message' => $request->message);

        $inbox_view_user = DB::table('inbox_view_user')->insertGetId($temp);


        if ($request->type == 2) {

            DB::table('inbox_view_advisor')->where('id', $inbox_view_advisor)->update(['chat_counter' => $counter]);

        } else {

            DB::table('inbox_view_user')->where('id', $inbox_view_user)->update(['chat_counter' => $counter]);

        }

        //  $advisor_Chats = DB::table('inbox_view_advisor') 
        //     ->where('sentBy', '=', $request->sentBy) 
        //     ->where('sentTo', '=', $request->sentTo) 
        //     ->delete(); 

        // //  $test2 = DB::table('inbox_view_advisor') 
        // //     ->where('sentBy', '=', $request->sentTo) 
        // //     ->where('sentTo', '=', $request->sentBy) 
        // //     ->delete(); 

        // $temp = array("sentBy" => $request->sentBy,'sentTo' =>$request->sentTo,'message' => $request->message);

        //  $inbox_view_user =  DB::table('inbox_view_advisor')->insertGetId($temp);

        //type 1 if  advisor sends message
        if ($request->type == 1) {

            $getUserName = DB::table('users')
                ->where('id', '=', $request->sentTo)
                // ->pluck('name','devicePlatform')
                ->first();

            $getAdvisorName = DB::table('advisors')
                ->where('id', '=', $request->sentBy)
                // ->pluck('legalNameOfIndividual')
                ->first();

            $body = array(
                'body' => $getAdvisorName->legalNameOfIndividual . ' sent you a message',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'chat_rate' => $getAdvisorName->TextChatRate,
                'profileImage' => $getAdvisorName->profileImage,
                'msg' => $request->message,
                'sender_id' => $request->sentBy,
                'reciever_id' => $request->sentTo,
                'type' => "chatMsg",
                'badge' => "1",
                'senderName' => $getAdvisorName->legalNameOfIndividual,
                'sound' => 1,
            );

            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $request->sentTo;

            if ($getUserName->devicePlatform == 2) {

                $util->sendPushNotificationToUserAndroid($userID, $title, $body);
            }
            if ($getUserName->devicePlatform == 1) {

                $util->sendPushNotificationToUser($userID, $title, $body);
            }
        }

        //type 2 if user  sends message
        if ($request->type == 2) {

            $getAdvisorName = DB::table('advisors')
                ->where('id', '=', $request->sentTo)
                ->first();

            $getUserName = DB::table('users')
                ->where('id', '=', $request->sentBy)
                ->first();


            // return json_encode($getUserName);

            $body = array(
                'body' => $getUserName->name . ' sent you a message',
                'title' => "Thesecretpsychics",
                'vibrate' => 1,
                'chat_rate' => NULL,
                'profileImage' => $getUserName->profileImage,
                'msg' => $request->message,
                'sender_id' => $request->sentBy,
                'reciever_id' => $request->sentTo,
                'type' => "chatMsg",
                'badge' => "1",
                'senderName' => $getUserName->name,
                'sound' => 1,
            );
            $util = new Utilclass();
            $title = 'Thesecretpsychics';
            $body = $body;
            $userID = $request->sentTo;
            if ($getAdvisorName->devicePlatform == 2) {

                $util->sendPushNotificationToAdvisorAndroid($userID, $title, $body);
            }
            if ($getAdvisorName->devicePlatform == 1) {

                $util->sendPushNotificationToAdvisor($userID, $title, $body);
            }


            //......Create Payments Record of the advisor

            $Payments = new Payments();
            $Payments->userId = $request->sentBy;
            $Payments->advisorId = $request->sentTo;
            $Payments->credit = $costToBeDeducted * 0.4;
            $Payments->admin_credit = number_format($costToBeDeducted, 1);
            $Payments->system_fee = $costToBeDeducted * 0.3;
            $Payments->refrence = 'Live Chat';
            $Payments->message_id = $chatID;
            $Payments->balance = number_format((float)$advisorCredit->advisor_credit + $costToBeDeducted * 0.4, 2, '.', '');
            $Payments->save();


            //  $util->sendPushNotificationToAdvisor($userID, $title, $body);

        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Chats Successfully Created', 'Result' => $Chats]);
    }

    public function update($id, Request $request)
    {
        $Category = Chats::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $Category->update($request->all());

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Departments is Updated', 'Result' => $Category]);
    }

    public function destroy($id, Request $request)
    {
        $Category = Chats::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $Category->delete();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Department deleted', 'Result' => NULL]);
    }
    //   public function show()
    // {
    //       $Messages=Chats::all();

    //       return response()->json(['statusCode'=>'1','statusMessage'=>'showing all Departments','Result'=>$Messages]);
    // }
    public function getConversationAdvisor(Request $request)
    {

        $sentTo = $request->input('sentTo');

        $advisor_Chats = DB::table('inbox_view_advisor')
            ->where('sentTo', '=', $sentTo)
            ->orwhere('sentBy', '=', $sentTo)
            ->orderBy('created_at', 'desc')
            // ->join('users','users.id','=','inbox_view_user.sentTo')
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'inbox_view_advisor.sentTo'); // i want to join the users table with either of these columns
                $join->orOn('users.id', '=', 'inbox_view_advisor.sentBy');
            })
            ->select('users.name', 'users.profileImage', 'users.credit', 'inbox_view_advisor.*')
            ->get();


        for ($i = 0; $i < count($advisor_Chats); $i++) {

            $advisor_Chats[$i]->{'userId'} = $sentTo;

        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all advisor chat', 'Result' => $advisor_Chats]);
    }

    public function getConversationuser(Request $request)
    {

        $sentBy = $request->input('sentBy');


        $sentBy1 = DB::table('inbox_view_user')
            ->where('sentBy', '=', $sentBy)
            ->orwhere('sentTo', '=', $sentBy)
            ->orderBy('created_at', 'desc')
            // ->join('advisors','advisors.id', '=','inbox_view_user.sentBy')
            ->join('advisors', function ($join) {
                $join->on('advisors.id', '=', 'inbox_view_user.sentTo'); // i want to join the users table with either of these columns
                $join->orOn('advisors.id', '=', 'inbox_view_user.sentBy');
            })
            ->select('advisors.screenName', 'advisors.profileImage', 'advisors.TextChatRate', 'advisors.legalNameOfIndividual', 'inbox_view_user.*')
            ->get();


        for ($i = 0; $i < count($sentBy1); $i++) {

            $sentBy1[$i]->{'userId'} = $sentBy;

        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat', 'Result' => $sentBy1]);
    }

    public function detailedChat(Request $request)
    {
        $sentBy = $request->input('sentBy');
        $sentTo = $request->input('sentTo');
        $type = $request->input('type');

        if ($type == 1) {

            DB::table('inbox_view_advisor')->where('sentBy', $sentBy)->where('sentTo', $sentTo)->update(array('chat_counter' => 0));
            DB::table('inbox_view_advisor')->where('sentBy', $sentTo)->where('sentTo', $sentBy)->update(array('chat_counter' => 0));


            DB::table('chats')->where('sentBy', $sentBy)->where('sentTo', $sentTo)->update(array('chatStatus' => 1));
            DB::table('chats')->where('sentBy', $sentTo)->where('sentTo', $sentBy)->update(array('chatStatus' => 1));
        }
        if ($type == 2) {

            DB::table('inbox_view_user')->where('sentBy', $sentBy)->where('sentTo', $sentTo)->update(array('chat_counter' => 0));
            DB::table('inbox_view_user')->where('sentBy', $sentTo)->where('sentTo', $sentBy)->update(array('chat_counter' => 0));


            DB::table('chats')->where('sentBy', $sentBy)->where('sentTo', $sentTo)->update(array('chatStatus' => 1));
            DB::table('chats')->where('sentBy', $sentTo)->where('sentTo', $sentBy)->update(array('chatStatus' => 1));
        }

        $detailedChat = Chats::where(function ($query) use ($sentBy, $sentTo) {
            $query->where('sentBy', $sentBy)
                ->Where('sentTo', $sentTo);
        })
            ->orWhere(function ($query) use ($sentBy, $sentTo) {
                $query->where('sentTo', $sentBy)
                    ->where('sentBy', $sentTo);
            })->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat', 'Result' => $detailedChat]);
    }

    public function getOrdersForWeb(Request $request)
    {
        $sentBy = $request->input('sentBy');
        $sentTo = $request->input('sentTo');
        $orderID = $request->input('order_id');
        $totalAmount = 0;

        $orders = Orders::join('payments', 'orders.id', '=', 'payments.order_id')
            ->select('admin_credit','orders.*')
            ->where('orders.id', '=', $orderID)
            ->get();
        $advisorDetailts = Advisors::where('id', $sentBy)->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")->first();
        $userDetailts = User::where('id', $sentTo)->select('id', 'name', 'profileImage')->first();
        $feedbackRating = AdvisorsReviews::where('orderId','=',$orderID)
            ->select('feedback','rating')
            ->first();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat',
            'Result' => ["orderDetails" => $orders, "totalAmount" => number_format($totalAmount, 2),
                "advisorDetails" => $advisorDetailts, "userDetails" => $userDetailts, "feedbackRating" => $feedbackRating]]);
    }

    public function getDeclinedOrdersForWeb(Request $request)
    {
        $sentBy = $request->input('sentBy');
        $sentTo = $request->input('sentTo');
        $orderID = $request->input('order_id');
        $totalAmount = 0;

        $orders = Orders::select('orders.*')
            ->where('orders.id', '=', $orderID)
            ->get();
        $advisorDetailts = Advisors::where('id', $sentBy)->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")->first();
        $userDetailts = User::where('id', $sentTo)->select('id', 'name', 'profileImage')->first();
        $feedbackRating = AdvisorsReviews::where('orderId','=',$orderID)
            ->select('feedback','rating')
            ->first();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat',
            'Result' => ["orderDetails" => $orders, "totalAmount" => number_format($totalAmount, 2),
                "advisorDetails" => $advisorDetailts, "userDetails" => $userDetailts, "feedbackRating" => $feedbackRating]]);
    }

    public function getConvoForWeb(Request $request)
    {
        $sentBy = $request->input('sentBy');
        $sentTo = $request->input('sentTo');
        $orderID = $request->input('order_id');
        $totalAmount = 0;

        $advisorDetailts = Advisors::where('id', $sentBy)->selectRaw("advisors.*, 
        (select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating")->first();
        $userDetailts = User::where('id', $sentTo)->select('id', 'name', 'profileImage')->first();

        $detailedChat = Chats::leftjoin('payments', 'chats.id', '=', 'payments.message_id')
            ->selectRaw("chats.*, payments.id as payment_id, payments.admin_credit, payments.payment_status")
            ->where(function ($query) use ($sentBy, $sentTo) {
                $query->where('sentBy', $sentBy)
                    ->Where('sentTo', $sentTo);
            })
            ->orWhere(function ($query) use ($sentBy, $sentTo) {
                $query->where('sentTo', $sentBy)
                    ->where('sentBy', $sentTo);
            })
            ->orderby('created_at', 'desc')
            ->get();
        $feedbackRating = AdvisorsReviews::where('orderId','=',$orderID)
            ->select('feedback','rating')
            ->first();

        foreach ($detailedChat as $item) {
            $totalAmount += $item->admin_credit;
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat',
            'Result' => ["totalAmount" => number_format($totalAmount, 2),
                "advisorDetails" => $advisorDetailts, "userDetails" => $userDetailts,
                "chatDetails" => $detailedChat, "feedbackRating" => $feedbackRating]]);
    }

    public function getWebChats()
    {
        $conversations = Conversation::join('users', 'users.id','=','conversations.user_id')
            ->join('advisors', 'advisors.id','=','conversations.advisor_id')
            ->select('conversations.id','users.id as user_id','advisors.id as advisor_id','name as client_name','screenName as advisor_name',
                'users.email as user_email','advisors.email as advisor_email',
                'users.profileImage as user_image', 'advisors.profileImage as advisor_image','conversations.updated_at')
            ->orderby('updated_at','desc')
            ->get();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all user chat',
            'Result' => [
                "conversations" => $conversations]]);
    }


    //for testing function

    public function getAppTimes(){
        $timerArray = collect(
            [
                ["type" => "Alpha", "timer" => 1],
                ["type" => "Bravo", "timer" => 3],
                ["type" => "Charlie", "timer" => 5],
                ["type" => "Tango", "timer" => 7],
                ["type" => "Zolo", "timer" => 9]
            ]
            );
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing All Timers',
            'Result' =>
                $timerArray]);
    }


}
 


