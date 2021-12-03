<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\AdminNotification;
use App\Advisor as Advisors;
use App\Experience;
use Modules\Advisor\Mail\AdvisorSupportMessage;
use App\Mail\ClientSupportMessage;
use App\Mail\SuggestionResponse;
use App\OrderingInstruction;
use App\User;
use App\Departments;
use App\CustomerSupport;
use App\PrivacyPolicies;
use App\AboutUs;
use App\DepartmentUsers;
use App\ClientCustomerSupports;
use App\TermsOfUse;
use App\Termsconditions;
use App\ProfileSetups;
use App\BecomingAnAdvisors;
use App\OurIdeas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use DB;

class customersupportController extends ApiController
{
    //   public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    //..........CustomerSupport

    public function store(Request $request)
    {
        $title = $request->get('heading');
        $message = $request->get('suggestion');
        $advisorID = $request->get('advisorId');
        $advisorDetails = Advisors::where('id', $advisorID)->select('screenName','email')->first();
        $CustomerSupport = CustomerSupport::create($request->all());
        // Mail::to($advisorDetails->email)->send(new AdvisorSupportMessage($title, $message, $advisorDetails->screenName));
        Mail::to('support@thesecretpsychics.com')->send(new AdvisorSupportMessage($title, $message, $advisorDetails->screenName));
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Customer Support Successfully Created', 'Result' => $CustomerSupport]);
    }

    public function getAdvisorsCustomerSupport(Request $request){
        $advisors = CustomerSupport::join('advisors', 'advisors.id','=','customer_supports.advisorId')
            ->select('customer_supports.id as support_id','customer_supports.created_at as support_created_at','advisors.*')
            ->orderby('customer_supports.created_at','desc')
            ->get();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing advisors for support messages', 'Result' => $advisors]);
    }

    public function getAdvisorSupportMessage(Request $request)
    {
        $CustomerSupport = DB::table('customer_supports')
            ->join('advisors', 'advisors.id', '=', 'customer_supports.advisorId')
            ->select('customer_supports.*', 'advisors.*')
            ->where('customer_supports.id','=',$request->get('id'))
            ->orderby('customer_supports.created_at','desc')
            ->first();

        // $CustomerSupport = CustomerSupport::all();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisor Customer Support Successfully retrieved', 'Result' => $CustomerSupport]);
    }

    //..........Client CustomerSupport

    public function getClientCustomerSupport(Request $request){
        $advisors = ClientCustomerSupports::join('users', 'users.id','=','client_customer_supports.userId')
            ->select('client_customer_supports.id as support_id','client_customer_supports.created_at as support_created_at','users.*')
            ->orderby('client_customer_supports.created_at','desc')
            ->get();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Clients for support messages', 'Result' => $advisors]);
    }

    public function userstore(Request $request)
    {
        $title = $request->get('heading');
        $message = $request->get('suggestion');
        $clientID = $request->get('userId');
        $clientDetails = User::where('id', $clientID)->select('name')->first();
        $ClientCustomerSupports = ClientCustomerSupports::create($request->all());
        Mail::to('support@thesecretpsychics.com')->send(new ClientSupportMessage($title, $message, $clientDetails->name));
        /*AdminNotification::insert([
            "client_id" => $clientID,"support_id" => $ClientCustomerSupports->id,
            "notification_title" => $title, "notification_msg" => $message,
            "notification_type" => 2
        ]);*/
        return response()->json(['statusCode' => '1', 'statusMessage' => 'user Customer Support Successfully Created', 'Result' => $ClientCustomerSupports]);
    }

    public function getUserCustomerSupportMessage(Request $request)
    {
        $ClientCustomerSupports = DB::table('client_customer_supports')
            ->join('users', 'users.id', '=', 'client_customer_supports.userId')
            ->select('client_customer_supports.*', 'users.*')
            ->where('client_customer_supports.id','=',$request->get('id'))
            ->first();

        // $CustomerSupport = CustomerSupport::all();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'user Customer Support Successfully retrieved', 'Result' => $ClientCustomerSupports]);
    }

    //sending suggestion to advisor or client from admin

    public function sendEmailToClientAdvisor(Request $request){
        $email = $request->get('email');
        $response = $request->get('admin_response');
        Mail::to($email)->send(new SuggestionResponse($response));
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Email sent successfully', 'Result' => null]);
    }



    //..........BecomingAnAdvisors

    public function storeBecomingAnAdvisors(Request $request)
    {
        // $BecomingAnAdvisors = BecomingAnAdvisors::update($request->all());
        $BecomingAnAdvisors = BecomingAnAdvisors::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'text' => $request->get('text')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Becoming An Advisor Successfully Created', 'Result' => $BecomingAnAdvisors]);
    }

    public function getBecomingAnAdvisors($id)
    {
        $BecomingAnAdvisors = DB::table('becoming_an_advisors')
            ->select('text')->where('id','=',$id)->first();
        // $CustomerSupport = CustomerSupport::all();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Becoming An Advisor Successfully retrieved', 'Result' => $BecomingAnAdvisors]);
    }

    //..........termsconditions

    public function storetermsconditions(Request $request)
    {
        // $BecomingAnAdvisors = BecomingAnAdvisors::update($request->all());
        $Termsconditions = Termsconditions::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'text' => $request->get('text')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => ' Terms conditions Successfully Created', 'Result' => $Termsconditions]);
    }

    public function gettermsconditions($id)
    {
        $Termsconditions = DB::table('termsconditions')
            ->select('text')->where('id','=',$id)->first();

        // $CustomerSupport = CustomerSupport::all();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Terms conditions Successfully retrieved', 'Result' => $Termsconditions]);
    }

    //..........profileSetups

    public function storeProfileSetups( Request $request)
    {
        $ProfileSetups = ProfileSetups::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'text' => $request->get('text')
        ]);

        return response()->json(['statusCode' => '1', 'statusMessage' => ' Profile Setups Successfully Created', 'Result' => $ProfileSetups]);
    }

    public function getProfileSetups($id)
    {
        $ProfileSetups = DB::table('profile_setups')
            ->select('text')->where('id','=',$id)->first();

        // $CustomerSupport = CustomerSupport::all();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Profile Setups Successfully retrieved', 'Result' => $ProfileSetups]);
    }


    //..........Terms Of Use

    public function terms_of_use(Request $request)
    {
        $TermsOfUse = TermsOfUse::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'termsOfUse' => $request->get('TermsOfUse')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Terms Of Use Successfully Created', 'Result' => $TermsOfUse]);
    }

    public function getterms_of_use($id)
    {
        $TermsOfUse = TermsOfUse::where('id', '=', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Terms Of Use Successfully retrieved', 'Result' => $TermsOfUse]);
    }

    //..........privacy_policies

    public function privacypolicies(Request $request)
    {
        $PrivacyPolicies = PrivacyPolicies::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'privacy' => $request->get('privacy')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Privacy Policies Successfully Created', 'Result' => $PrivacyPolicies]);
    }

    public function getprivacypolicies($id)
    {
        $PrivacyPolicies = PrivacyPolicies::where('id', '=', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Privacy Policies Successfully retrieved', 'Result' => $PrivacyPolicies]);
    }

    //..........AboutUs

    public function addAbouUs(Request $request)
    {
        $AboutUs =  AboutUs::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'aboutUs' => $request->get('aboutUs')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'About Us Successfully updated', 'Result' => $AboutUs]);
    }

    public function getAbouUs($id)
    {
        $AboutUs = AboutUs::where('id', '=', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'About us Successfully retrieved', 'Result' => $AboutUs]);
    }

    public function orderingInstruction(Request $request){
        $orderingInstructions = OrderingInstruction::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'text' => $request->get('text')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Ordering Instruction Successfully updated', 'Result' => $orderingInstructions]);
    }

    public function getorderingInstruction($id)
    {
        $orderingInstructions = OrderingInstruction::where('id', '=', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Ordering Instruction Successfully retrieved', 'Result' => $orderingInstructions]);
    }

    public function experience(Request $request){
        $experience = Experience::updateOrCreate([
            'id' => $request->get('id'),
        ], [
            'text' => $request->get('text')
        ]);
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Experience Successfully updated', 'Result' => $experience]);
    }

    public function getexperience($id)
    {
        $experience = Experience::where('id', '=', $id)->first();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Experience Successfully retrieved', 'Result' => $experience]);
    }


}
 