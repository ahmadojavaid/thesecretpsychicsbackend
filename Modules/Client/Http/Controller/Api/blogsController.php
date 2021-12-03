<?php

namespace Modules\Client\Http\Controller\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use App\Activities;
use App\Blogs;
use Illuminate\Support\Facades\Mail;
use DB;
use GuzzleHttp\Client;

class blogsController extends ApiController
{

    //   public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

// blog_author
// blog_image
// author_image
    public function show(Request $request)
    {
        $blogType = $request->get('blog_type');
        // return 'ss';
        // $Messages=Blogs::all();

        $Messages = DB::table('blogs')
            ->where('blog_type','=',$blogType)
            ->orderBy('created_at', 'desc')
            ->get();


        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all Blogss', 'Result' => $Messages]);
    }

    public function showSingle($id)
    {
        $Category = Blogs::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing single Blog', 'Result' => $Category]);
    }


    public function store(Request $request)
    {
        try {

            $Blogs = new Blogs();

            // return $request;
            if ($request->has('author_image') && $request->has('blog_image')) {
                // return 'sss';
                $unique1 = bin2hex(openssl_random_pseudo_bytes(10));
                $unique2 = bin2hex(openssl_random_pseudo_bytes(15));

                $format = '.png';

                $entityBody1 = $request['author_image'];// file_get_contents('php://input');
                $entityBody2 = $request['blog_image'];// file_get_contents('php://input');

                $Activities = Blogs::create($request->except(['author_image', 'blog_image']));
                // return $Activities;
                $imageName1 = $Activities->id . $unique1 . $format;
                $imageName2 = $Activities->id . $unique2 . $format;

                $directory = "/images/BlogsImages/";

                $path = base_path() . "/public" . $directory;

                $data1 = base64_decode($entityBody1);
                $data2 = base64_decode($entityBody2);

                file_put_contents($path . $imageName1, $data1);
                file_put_contents($path . $imageName2, $data2);

                $response1 = $directory . $imageName1;
                $response2 = $directory . $imageName2;

                $Activities->author_image = $response1;

                $Activities->blog_image = $response2;

                $Activities->save();
            } else {
                $Blogs = Blogs::create($request->except(['author_image', 'blog_image']));
                return response()->json(['statusCode' => '1', 'statusMessage' => 'Blogs Created', 'Result' => $Blogs]);

            }


            return response()->json(['statusCode' => '1', 'statusMessage' => 'Blogs Created', 'Result' => $Activities]);

        } catch (Illuminate\Database\QueryException $e) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        } catch (PDOException $e) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Some thing went wrong', 'error' => $e->getMessage()]);
        }
    }

    public function update($id, Request $request)
    {
        $Category = Blogs::find($id);
        $Category->update($request->except(['blog_image', 'author_image']));

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        // return $request;
        if ($request->has('author_image') && $request->author_image != NULL) {

            // $Category->update($request->except(['author_image']));
            $Category->update($request->except(['blog_image', 'author_image']));
            // return $request;
            $unique = bin2hex(openssl_random_pseudo_bytes(10));

            // $format = $request->input('content_type');
            $format = '.png';

            $entityBody = $request['author_image'];// file_get_contents('php://input');

            $imageName = $Category->id . $unique . $format;

            $directory = "/images/BlogsImages/";

            $path = base_path() . "/public" . $directory;

            $data = base64_decode($entityBody);

            file_put_contents($path . $imageName, $data);

            $response = $directory . $imageName;

            $Category->author_image = $response;

            DB::table('blogs')->where('id', $id)->update(array('author_image' => $response));
        }
        if ($request->has('blog_image') && $request->blog_image != NULL) {

            // $Category->update($request->except(['blog_image']));
            $Category->update($request->except(['blog_image', 'author_image']));

            $unique = bin2hex(openssl_random_pseudo_bytes(10));

            // $format = $request->input('content_type');
            $format = '.png';

            $entityBody = $request['blog_image'];// file_get_contents('php://input');

            $imageName = $Category->id . $unique . $format;

            $directory = "/images/BlogsImages/";

            $path = base_path() . "/public" . $directory;

            $data = base64_decode($entityBody);

            file_put_contents($path . $imageName, $data);

            $response = $directory . $imageName;

            $Category->blog_image = $response;

            DB::table('blogs')->where('id', $id)->update(array('blog_image' => $response));
        } else {
            $Category->update($request->except(['blog_image', 'author_image']));
        }

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Blogs Data is Updated', 'Result' => $Category]);


    }

    public function destroy($id, Request $request)
    {
        $Category = Blogs::find($id);

        if (!$Category) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Record Not Found', 'Result' => NULL]);
        }
        $Category->delete();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'Blogs deleted', 'Result' => NULL]);
    }

    public function notify(Request $request)
    {

        // return phpinfo();

        $util = new Utilclass();
        $title = 'helo';
        $body = 'testing';
        $userID = 6;

        $util->sendPushNotification($userID, $title, $body);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'notification send', 'Result' => $util]);
    }
    
    
    
    /** 
     * Blog API's 
     */

    public function blogPreview()
    {
        $blogs = Blogs::select('id','blog_title','blog_image')->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing all blog previews', 'Result' => $blogs]);
    }

    public function singleBlogDetails(Request $request)
    {
        $blogID = $request->get('blog_id');
        $blog = Blogs::find($blogID);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing blog post details', 'Result' => $blog]);
    }


}