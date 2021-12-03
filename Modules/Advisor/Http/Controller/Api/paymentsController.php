<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\Advisors;
use App\Orders;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use App\Payments;
use Illuminate\Support\Facades\Mail;
use Modules\Advisor\Http\Controller\Api\ApiController;

class paymentsController extends ApiController
{

    /**
     * Payment status and meaning
     * status 0 = Order is pending for withdrawal
     * status 1 = Completed and Withdrawal is successful from PayPal
     * status 2 = 60 days are completed / amount is requested by advisor for withdraw to admin
     * status 3 = Order is refunded
     * status 4 = Order is flagged
     */

    /**
     * Order Status and meaning
     * status 0 = pending
     * status 1 = completed
     * status 3 = cancelled
     * status 4 = flagged
     * status 5 = refunded
     */

    public function show(Request $request)
    {
        $advisorId = $request->input('advisorId');
        $totalWithdrawabable = 0;
        $totalEarnings = 0;
        $preWithdrawAmount = 0;
        $totalPending = 0;

        $calTotalEarnings = DB::table('payments')
            ->selectRaw('sum(payments.credit)as total_earning')
            ->where('advisorId', $advisorId)
            ->where('payment_status', 1)
            ->get();
        if (count($calTotalEarnings) > 0)
            $totalEarnings = $calTotalEarnings[0]->total_earning;

        /*$previouslyWithdrawn = DB::table('payments')
            ->selectRaw('payments.credit as previously_withdrawn')
            ->where('advisorId', $advisorId)
            ->where('payment_status', 1)
            ->orderby('id','desc')
            ->limit(1)
            ->get();

        if (count($previouslyWithdrawn) > 0)
            $preWithdrawAmount = $previouslyWithdrawn[0]->previously_withdrawn;*/

        $previouslyWithdrawn = DB::table('payment_withdrawed')
            ->select('amount')
            ->where('advisor_id', $advisorId)
            ->where('payment_status', 2)
            ->orderby('created_at', 'desc')
            ->limit(1)
            ->get();
        if (count($previouslyWithdrawn) > 0)
            $preWithdrawAmount = $previouslyWithdrawn[0]->amount;

        $clearedPaymentDetails = DB::table('payments')
            ->selectRaw("name,payments.id,userId,order_id,advisorId,payments.credit,admin_credit,FORMAT(balance, 2) as balance,
            refrence,message_id,system_fee,payment_status,payments.created_at")
            ->join('users', 'users.id', '=', 'payments.userId')
            ->where('advisorId', $advisorId)
            ->where('payment_status', 1)
            ->orderby('id', 'desc')
            ->latest('payments.created_at')
            ->get();

        $pendingPaymentsCal = DB::table('payments')
            ->selectRaw('id,payments.credit,created_at,advisorId,userId,payment_status')
            ->where('advisorId', $advisorId)
            ->whereIn('payment_status', [0, 3, 4])
            ->get();
        $pendingPaymentDetails = DB::table('payments')
            ->selectRaw("name,payments.id,userId,order_id,advisorId,payments.credit,admin_credit,FORMAT(balance, 2) as balance,
            refrence,message_id,system_fee,payment_status,payments.created_at")
            ->join('users', 'users.id', '=', 'payments.userId')
            ->where('advisorId', $advisorId)
            ->whereIn('payment_status', [0, 3, 4])
            ->orderby('id', 'desc')
            ->latest('payments.created_at')
            ->get();

        //$date1 = \Carbon\Carbon::today()->subDays(60);
        $ldate = date('Y-m-d H:i:s');
        $date5 = \Carbon\Carbon::parse($ldate);
        $date1 = $date5->subDays(60);
        foreach ($pendingPaymentsCal as $item) {
            if ($item->payment_status != 3) {
                $totalPending += $item->credit;
                if ($item->created_at <= $date1) {
                    $totalWithdrawabable += $item->credit;
                }
            }
        }

        return response()->json([
            'statusCode' => '1', 'statusMessage' => 'showing orders details',
            'Result' => ['totalEarning' => number_format((float)$totalEarnings, 2, '.', ''),
                'previouslyWithdrawn' => number_format((float)$preWithdrawAmount, 2, '.', ''),
                'pendingClearance' => number_format((float)($totalPending - $totalWithdrawabable), 2, '.', ''),
                'WithdrawabableAmount' => number_format((float)$totalWithdrawabable, 2, '.', ''),
                'clearedPaymentDetails' => $clearedPaymentDetails, 'pendingPayments' => $pendingPaymentDetails]
        ]);
    }

    public function withdrawAmount(Request $request)
    {
        $advisorId = $request->input('advisorId');
        $totalWithdrawabable = 0;
        $lastPaymentID = 0;

        $pendingPaymentsCal = DB::table('payments')
            ->selectRaw('id,payments.credit,created_at,advisorId,userId,payment_status')
            ->where('advisorId', $advisorId)
            ->whereIn('payment_status', [0, 4])
            ->get();

        if (!$pendingPaymentsCal) {

            return response()->json(['statusCode' => '1', 'statusMessage' => 'No Amount Available to Withdraw!', 'result' => null]);
        } else {

            $currentAdvisorCredit = DB::table('advisors')
                ->where('id', '=', $advisorId)
                ->pluck('advisor_credit')
                ->first();

            //$date1 = \Carbon\Carbon::today()->subDays(60);
            $ldate = date('Y-m-d H:i:s');
            $date5 = \Carbon\Carbon::parse($ldate);
            $date = $date5->subDays(60);
            //$date = Date('Y-m-d', strtotime($date1));
            foreach ($pendingPaymentsCal as $item) {
                if ($item->created_at <= $date) {
                    $totalWithdrawabable += $item->credit;
                }
            }
            $paymentWithdrawID = DB::table('payment_withdrawed')->insertGetId([
                "advisor_id" => $advisorId, "amount" => $totalWithdrawabable, "payment_status" => 1
            ]);
            if ($totalWithdrawabable != 0 && $totalWithdrawabable >= 50) {
                DB::table('advisors')->where('id', $advisorId)->update(array('advisor_credit' => $currentAdvisorCredit - $totalWithdrawabable));
                foreach ($pendingPaymentsCal as $item) {
                    if ($item->created_at <= $date) {
                        DB::table('payments')
                            ->where('id', '=', $item->id)
                            ->update([
                                "payment_status" => 2
                            ]);
                        $lastPaymentID = $item->id;
                        DB::table('payment_withdraw_track')->insert([
                            "payment_withdrawal_id" => $paymentWithdrawID,
                            "payment_id" => $item->id
                        ]);
                    }
                }
                $paymentDetails = DB::table('payments')
                    ->where('id', '>', $lastPaymentID)
                    ->where('advisorId', $advisorId)
                    ->where('payment_status', '=', 0)
                    ->select('id')
                    ->get();
                if (count($paymentDetails) > 0) {
                    foreach ($paymentDetails as $item) {
                        $paymentObj = Payments::where('id', '=', $item->id)->first();
                        $tempBalance = $paymentObj->balance;
                        if ($tempBalance > 0) {
                            $newBalance = round($tempBalance - $totalWithdrawabable);
                            if ($newBalance > 0) {
                                $paymentObj->balance = $newBalance;
                            } else {
                                $paymentObj->balance = 0;
                            }
                            $paymentObj->save();
                        }
                    }
                }
                /*DB::table('payments')
                    ->insert([
                        "advisorId" => $advisorId,
                        "credit" => $totalWithdrawabable,
                        "refrence" => 'Amount Withdraw',
                        "payment_status" => 2,
                        "balance" => $currentAdvisorCredit
                    ]);*/
                return response()->json(['statusCode' => '1', 'statusMessage' => 'Amount Withdraw Successful', 'result' => null]);
            } else {
                return response()->json(['statusCode' => '0', 'statusMessage' => 'No Amount Available to Withdraw or amount is less than 50!', 'result' => null]);
            }
        }

    }

    public function paymentListingForWeb()
    {
        $advisorDetailts = Advisors::join('payment_withdrawed', 'payment_withdrawed.advisor_id', '=', 'advisors.id')
            ->select(DB::raw("(select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating,
            advisors.*,payment_withdrawed.id as withdraw_id,
              amount as withrawable_amount, payment_withdrawed.created_at as payment_date"))
            ->where('payment_withdrawed.payment_status', 1)
            ->having('withrawable_amount', '>', 50)
            ->orderby('payment_withdrawed.created_at', 'desc')
            ->get();
        if (count($advisorDetailts) > 0) {
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Withdrawal Payments', 'result' => $advisorDetailts]);
        } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'No Payment Records Found', 'result' => null]);
        }

    }

    public function clearedPaymentListingForWeb()
    {
       /* $advisorDetailts = Advisors::join('payments', 'payments.advisorId', '=', 'advisors.id')
            ->select(DB::raw("(select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating,
            advisors.*"))
            ->where('payment_status', '=', 1)
            ->groupby('advisors.id')
            ->orderby('payments.created_at', 'desc')
            ->get();*/
        $advisorDetailts = Advisors::join('payment_withdrawed', 'payment_withdrawed.advisor_id', '=', 'advisors.id')
            ->select(DB::raw("(select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating,
             advisors.*, payment_withdrawed.amount,payment_withdrawed.created_at as payment_date"))
            ->where('payment_status', '=', 2)
            ->orderby('payment_withdrawed.created_at', 'desc')
            ->get();

        if (count($advisorDetailts) > 0) {
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing approved payments', 'result' => $advisorDetailts]);
        } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'No Payment Records Found', 'result' => null]);
        }

    }

    public function fetchAdvisorClearedPayments($id)
    {
        /*$advisorDetailts = Advisors::join('payments', 'payments.advisorId','=','advisors.id')
            ->select(DB::raw("(select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating,
            advisors.id as advisor_id,screenName,profileImage,payments.*"))
            ->where('advisors.id','=',$id)
            ->where('payment_status','=',1)
            ->orderby('payments.created_at','desc')
            ->get();*/

        $advisorDetailts = Advisors::join('payment_withdrawed', 'payment_withdrawed.advisor_id', '=', 'advisors.id')
            ->select(DB::raw("(select AVG(rating) from advisors_reviews where advisors.id = advisors_reviews.advisorId) as advisor_rating,
            advisors.id as advisor_id,screenName,profileImage,payment_withdrawed.amount,payment_withdrawed.created_at"))
            ->where('advisors.id', '=', $id)
            ->where('payment_status', '=', 2)
            ->orderby('payment_withdrawed.created_at', 'desc')
            ->get();
        if (count($advisorDetailts) > 0) {
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing approved payments', 'result' => $advisorDetailts]);
        } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'No Payment Records Found', 'result' => null]);
        }

    }

    public function paymentWithdraw(Request $request)
    {
        $advisorId = $request->input('advisorId');
        $withdrawId = $request->input('withdraw_id');

        $paymentIDs = DB::table('payment_withdraw_track')
            ->select('payment_id')
            ->where('payment_withdrawal_id', '=', $withdrawId)
            ->get();

        foreach ($paymentIDs as $item) {
                DB::table('payments')
                    ->where('id', '=', $item->payment_id)
                    ->update([
                        "payment_status" => 1
                    ]);
        }

        DB::table('payment_withdrawed')
            ->where('advisor_id', '=', $advisorId)
            ->where('payment_status', '=', 1)
            ->where('id', '=', $withdrawId)
            ->update([
                "payment_status" => 2
            ]);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Amount Withdraw Successful Via PayPal', 'result' => null]);

    }

    public function earningsReport(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $advisorID = $request->get('advisorId');

        $payments = Payments::join('advisors', 'advisors.id', '=', 'payments.advisorId')
            ->selectRaw("screenName as NAME,DATE_FORMAT(payments.created_at, '%M %d, %Y %h:%i:%s') as DATE,refrence as REFERENCE,credit as CREDIT,balance as BALANCE")
            ->whereRaw("DATE(payments.created_at) BETWEEN $from AND $to")
            ->where('payment_status', '=', 1)
            ->where('advisorId', '=', $advisorID)
            ->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing earnings between given dates', 'result' => $payments]);
    }

    public function refundClient(Request $request)
    {
        $orderID = $request->get('order_id');
        $ldate = date('Y-m-d H:i:s');
        $date5 = \Carbon\Carbon::parse($ldate);
        $date1 = $date5->subDays(60);
        //$date = Date('Y-m-d', strtotime($date1));
        $orderDetails = Payments::where('order_id', '=', $orderID)
            ->where("created_at", '>=', $date1)
            ->where("payment_status", '!=', 3)
            ->first();

        if (!empty($orderDetails)) {
            $getBalanceOfUser = DB::table('users')
                ->where('id', '=', $orderDetails->userId)
                ->pluck('credit')
                ->first();

            $getBalanceOfAdvisor = DB::table('advisors')
                ->where('id', '=', $orderDetails->advisorId)
                ->pluck('advisor_credit')
                ->first();

            DB::table('users')->where('id', $orderDetails->userId)->update(array('credit' => $getBalanceOfUser + 9.99));
            DB::table('advisors')->where('id', $orderDetails->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor - 9.99 * 0.4));

            $paymentsID = DB::table('payments')
                ->where('order_id', '=', $orderID)
                ->select('id')
                ->first();
            $paymentDetails = DB::table('payments')
                ->where('id', '>=', $paymentsID->id)
                ->where('advisorId', $orderDetails->advisorId)
                ->where('userId', $orderDetails->userId)
                ->select('id', 'refrence')
                ->get();
            if (count($paymentDetails) > 0) {
                foreach ($paymentDetails as $item) {
                    $paymentObj = Payments::where('id', '=', $item->id)->first();
                    $tempBalance = $paymentObj->balance;
                    if ($tempBalance > 0) {
                        $newBalance = round($tempBalance - 9.99 * 0.4);
                        if ($newBalance > 0) {
                            $paymentObj->balance = $newBalance;
                        } else {
                            $paymentObj->balance = 0;
                        }
                        $paymentObj->save();
                    }
                }
            }
            DB::table('payments')
                ->where('id', '=', $paymentsID->id)
                ->update([
                    "refrence" => 'Refund', "payment_status" => 3
                ]);
            $orderDetails = Orders::find($orderID);
            $orderDetails->isCompleted = 5;
            $orderDetails->save();

            return response()->json(['statusCode' => '1', 'statusMessage' => 'Payment Refunded Successfully', 'result' => null]);
        } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Order is older than 60 days', 'result' => null]);
        }
    }

    public function refundChatClient(Request $request)
    {
        $paymentID = $request->get('payment_id');
        $ldate = date('Y-m-d H:i:s');
        $date5 = \Carbon\Carbon::parse($ldate);
        $date1 = $date5->subDays(60);
        //$date = Date('Y-m-d', strtotime($date1));
        $paymentDetails = Payments::where('id', '=', $paymentID)
            ->where("created_at", '>=', $date1)
            ->where("payment_status", "!=", 3)
            ->first();

        if (!empty($paymentDetails)) {
            $getBalanceOfUser = DB::table('users')
                ->where('id', '=', $paymentDetails->userId)
                ->pluck('credit')
                ->first();

            $getBalanceOfAdvisor = DB::table('advisors')
                ->where('id', '=', $paymentDetails->advisorId)
                ->pluck('advisor_credit')
                ->first();

            $amountToBeAdded = $paymentDetails->admin_credit;
            $amountToBeDeducted = $paymentDetails->credit;
            DB::table('users')->where('id', $paymentDetails->userId)->update(array('credit' => $getBalanceOfUser + $amountToBeAdded));
            DB::table('advisors')->where('id', $paymentDetails->advisorId)->update(array('advisor_credit' => $getBalanceOfAdvisor - $amountToBeDeducted));

            $paymentDetailsAfterDate = DB::table('payments')
                ->where('id', '>=', $paymentDetails->id)
                ->where('advisorId', $paymentDetails->advisorId)
                ->where('userId', $paymentDetails->userId)
                ->select('id', 'refrence')
                ->get();
            if (count($paymentDetailsAfterDate) > 0) {
                foreach ($paymentDetailsAfterDate as $item) {
                    $paymentObj = Payments::where('id', '=', $item->id)->first();
                    $tempBalance = $paymentObj->balance;
                    if ($tempBalance > 0) {
                        $newBalance = round($tempBalance - $amountToBeDeducted);
                        if ($newBalance > 0) {
                            $paymentObj->balance = $newBalance;
                        } else {
                            $paymentObj->balance = 0;
                        }
                        $paymentObj->save();
                    }
                }
            }
            DB::table('payments')
                ->where('id', '=', $paymentDetails->id)
                ->update([
                    "refrence" => 'Refund', "payment_status" => 3
                ]);

            return response()->json(['statusCode' => '1', 'statusMessage' => 'Payment Refunded Successfully', 'result' => null]);
        } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Message is older than 60 days', 'result' => null]);
        }
    }

    public function revenueGraph()
    {
        /*$totalRevenue = Payments::selectRaw("MONTH(created_at) as Month,ROUND(sum(admin_credit * 0.4)) as advisor_total,
        ROUND(sum(admin_credit * 0.3)) as company_total,
        ROUND(sum(admin_credit * 0.3)) as services_total")
            ->groupby(DB::raw("MONTH(created_at)"))
            ->orderByRaw("MONTH(created_at)")
            ->get();*/

        //executing procedure from DB
        $totalRevenue = DB::select('call advisor_day_wise_revenue()');
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Show monthly revenue details', 'result' => $totalRevenue]);

    }

    public function revenueDayWiseGraph()
    {
        $currentMonth = date("m");
        $currentYear = date("Y");
        $noOfDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $result = array();
        $totalRevenue = Payments::selectRaw("DAY(created_at) as Day,IFNULL(ROUND(sum(admin_credit * 0.4)),0) as advisor_total,
        IFNULL(ROUND(sum(admin_credit * 0.3)),0) as company_total,
        IFNULL(ROUND(sum(admin_credit * 0.3)),0) as services_total")
            ->whereRaw("MONTH(created_at) = $currentMonth")
            ->groupby(DB::raw("DAY(created_at)"))
            ->orderByRaw("DAY(created_at)")
            ->get();

        /*if(count($totalRevenue) > 0){
            for($i = 1; $i <= $noOfDays; $i++){
                if($totalRevenue[$i]->Day - $i == 0){}
            }
        }*/
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Show revenue details', 'result' => $totalRevenue]);

    }


}
