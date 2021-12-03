<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
class AuthController extends Controller
{
	use AuthenticatesUsers;

    public function loginViewAdmin(){
    	return view('admin.auth.index');
    }
    public function adminLogin(Request $request){
    	$this->validateLogin($request);
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if(Auth::guard('admin_web')->attempt(['email' => $request->email, 'password' => $request->password])) {
            if(Auth::guard('admin_web')->user()){
                request()->session()->put('flash_success','Login Successfully');
                request()->session()->save();
                return redirect()->route('admin.dashboard');
            }else{
                Auth::guard('admin_web')->logout();
                request()->session()->put('flash','Your Credential is wrong.');
                request()->session()->save();
                return redirect()->back();
            }
        }else {
            request()->session()->put('flash','Your Credential is wrong.');
            request()->session()->save();
            return redirect()->back();
        }
    }
    public function adminLogout(){
    	if(Auth::guard('admin_web')->user()){
    		Auth::guard('admin_web')->logout();
    		request()->session()->put('flash_success','Logout Successfully');
            request()->session()->save();
            return redirect()->route('ad_login');
    	}else{
    		request()->session()->put('flash','Something went wrong.');
            request()->session()->save();
            return redirect()->back();
    	}
    }
}
