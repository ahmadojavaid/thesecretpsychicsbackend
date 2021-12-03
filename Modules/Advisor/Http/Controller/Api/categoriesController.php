<?php

namespace Modules\Advisor\Http\Controller\Api;

use App\AdvisorCategories;
use App\User;
use App\Categories;
use App\Advisor as Advisors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Modules\Advisor\Helper\Utilclass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use DB;
use Carbon\Carbon;


class categoriesController extends ApiController
{
    //   public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

//..........Department 

    public function store(Request $request)
    {
        // $Categories = Categories::create($request->all());

        $Categories = new Categories();

        if ($request->has('catImage') && $request->has('appIcons')) {

            $unique1 = bin2hex(openssl_random_pseudo_bytes(10));
            $unique2 = bin2hex(openssl_random_pseudo_bytes(10));

            $format = '.png';

            $entityBody1 = $request['catImage'];// file_get_contents('php://input');
            $entityBody2 = $request['appIcons'];// file_get_contents('php://input');

            $Categories = Categories::create($request->except(['catImage', 'appIcons']));

            $imageName1 = $Categories->id . $unique1 . $format;
            $imageName2 = $Categories->id . $unique2 . $format;

            $directory = "/images/categoryImages/";

            $path = base_path() . "/public" . $directory;

            /*$data1 = base64_decode($entityBody1);
            $data2 = base64_decode($entityBody2);*/

            $entityBody1->move($path, $imageName1);
            $entityBody2->move($path, $imageName2);
            /*file_put_contents($path . $imageName1, $data1);
            file_put_contents($path . $imageName2, $data2);*/

            $response1 = $directory . $imageName1;
            $response2 = $directory . $imageName2;

            $Categories->catImage = $response1;
            $Categories->appIcons = $response2;

            $Categories->save();
        } else {
            $Categories->create($request->except(['catImage']));

            return response()->json(['statusCode' => '1', 'statusMessage' => 'Category Successfully Created', 'Result' => $Categories]);

        }
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Category Successfully Created', 'Result' => $Categories]);
    }

    public function update($id, Request $request)
    {
        $Category = Categories::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $Category->update($request->all());

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Departments is Updated', 'Result' => $Category]);
    }

    public function destroy($id)
    {
        $Category = Categories::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $Category->delete();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Category deleted', 'Result' => NULL]);
    }

    public function show()
    {
        $Messages = Categories::all();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all Departments', 'Result' => $Messages]);
    }

    public function showPsychicsByCat(Request $request)
    {

        $CategoryId = $request->input('categoryId');

        $advisor_categories = DB::table('advisor_categories')
            ->where('categoryId', '=', $CategoryId)
            ->pluck('advisorId');

        $Advisors = new Advisors();

        $temp = $Advisors->getData($advisor_categories);

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


        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all Psychics By Cat', 'All psychics' => $temp]);
    }

    public function showPsychicsByCatSearch(Request $request)
    {
        $catName = $request->input('catName');

        $categories = DB::table('categories')
            ->Where('categoryName', 'LIKE', '%' . $catName . '%')
            ->pluck('id');

        $advisor_categories = DB::table('advisor_categories')
            ->whereIn('categoryId', $categories)
            ->pluck('advisorId');

        $Advisors = new Advisors();

        $temp = $Advisors->getData($advisor_categories);

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

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all Psychics By Cat Search', 'Result' => $temp]);
    }

    public function assignCat(Request $request){
        $catgories = $request->input('categories');
        $advisorID = $request->input('advisorId');

        if(count($catgories) > 0){
            foreach($catgories as $cat){
                AdvisorCategories::insert([
                    "advisorId" => $advisorID,
                    "categoryId" => $cat
                ]);
            }
            return response()->json(['statusCode' => '2', 'statusMessage' => 'Categories Assigned Successfully', 'Result' => null]);
        }
        else{
            return response()->json(['statusCode' => '3', 'statusMessage' => 'Categories Assigned Successfully', 'Result' => null]);
        }
    }

}
 


