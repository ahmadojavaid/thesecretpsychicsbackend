<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Advisor;
use App\User;
use App\Orders;
class DashboardController extends Controller
{
    public function index(){
    	// dd(Auth::guard('admin_web')->user());
    	$orders['total_advisors'] 		= Advisor::where('profileStatus', 1)->count();
        $orders['pending_advisors'] 	= Advisor::where('account_status',0)->where('profileStatus', 1)->count();
        $orders['approved_advisors'] 	= Advisor::where('account_status',1)->where('profileStatus', 1)->count();
        $orders['deactivated_advisors'] = Advisor::where('account_status',2)->count();
        $orders['active_users'] 		= User::where('account_status',1)->count();
        $orders['active_users'] 		= User::where('account_status',1)->count();
        $orders['deactivated_users'] 	= User::where('account_status',0)->count();

        $orders['total_orders']			= Orders::count();
        $orders['completed_orders'] 	= Orders::where('isCompleted', 1)->count();
        $orders['pending_orders'] 		= Orders::where('isCompleted', 0)->count();
        $orders['decline_orders'] 		= Orders::where('isCompleted', 3)->count();
        $orders['flagged_orders'] 		= Orders::where('isCompleted', 4)->count();
    	return view('admin.pages.dashboard',compact('orders'));
    }
    
}
