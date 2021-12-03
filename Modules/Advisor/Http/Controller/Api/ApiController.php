<?php

namespace Modules\Advisor\Http\Controller\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Advisor\Helper\APIHELPER;

class ApiController extends Controller
{
	public $apiHelper;
    public function __construct(){
		$this->apiHelper = new APIHELPER();
	}
}
