<?php

namespace Modules\Client\Http\Controller\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Client\Helper\APIHELPER;

class ApiController extends Controller
{
	public $apiHelper;
    public function __construct(){
		$this->apiHelper = new APIHELPER();
	}
}
