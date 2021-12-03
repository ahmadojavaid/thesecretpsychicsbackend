<?php

namespace Modules\Advisor\Http\Controller\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use App\Advisor as Advisors;
use App\AdvisorsReviews;
use GuzzleHttp\Client;
use Log;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Client as GuzzleHttpClient;
use DB;
// use Excel;
use Maatwebsite\Excel\Facades\Excel;

class advisorreviewController extends ApiController
{

    public function store(Request $request)
    {

        $AdvisorsReviews = AdvisorsReviews::create($request->all());
        $AdvisorsReviews->orderId = $request->get('orderId');
        $AdvisorsReviews->save();


        $feedbackCount = DB::table('advisors')->where('id', '=', $request->input('advisorId'))->pluck('feedbackCount')->first();

        $addOne = $feedbackCount + 1;

        DB::table('advisors')->where('id', '=', $request->input('advisorId'))->update(array('feedbackCount' => $addOne));


        DB::table('orders')->where('id', '=', $request->input('orderId'))->update(array('isReviewed' => 1));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors Reviews Successfully Created', 'Result' => $AdvisorsReviews]);
    }

    public function show(Request $request)
    {
        $AdvisorsReviews = AdvisorsReviews::all();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors Reviews Successfully retrieved', 'Result' => $AdvisorsReviews]);
    }

    public function showReview($id)
    {
        $AdvisorsReviews = AdvisorsReviews::find($id);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Advisors Review Successfully retrieved', 'Result' => $AdvisorsReviews]);
    }

    public function advisorReviewsForWeb()
    {
        $reviews = AdvisorsReviews::join('advisors', 'advisors.id','=','advisors_reviews.advisorId')
        ->join('users', 'users.id','=','advisors_reviews.userId')
        ->select('users.name as clientName','users.profileImage as clientImage','advisors.screenName as advisorName',
            'advisors.profileImage as advisorImage','advisors_reviews.*')
            ->orderby('advisors_reviews.created_at','desc')
            ->get();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing Advisor Reviews', 'Result' => $reviews]);
    }

    public function updateReview($id, Request $request){
        $reviews = AdvisorsReviews::find($id);
        $reviews->feedback = $request->get('feedback');
        $reviews->rating = $request->get('rating');
        $reviews->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Review Updated Successfully', 'Result' => $reviews]);
    }

    public function bulkDelete(Request $request){
        $reviewIDs = $request->get('review_id');

        if(count($reviewIDs) > 0){
            AdvisorsReviews::destroy($reviewIDs);
            return response()->json(['statusCode' => '1', 'statusMessage' => 'Review Deleted Successfully', 'Result' => null]);
        }
        else{
            return response()->json(['statusCode' => '0', 'statusMessage' => 'No data found', 'Result' => null],422);
        }
    }

}