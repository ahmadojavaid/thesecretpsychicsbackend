<?php
 

namespace Modules\Client\Http\Controller\Api;


use App\SelfHelpVideo;

class HelpVideosController extends ApiController
{
    public function getAllVids()
    {
        $videos = SelfHelpVideo::join('categories', 'categories.id','=','self_help_videos.category_id')
        ->select('self_help_videos.*','categoryName')->get();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Showing all videos', 'Result' => $videos]);
    }



    public function view()
    {
        return view('admin.dashboard.index');
    }
}